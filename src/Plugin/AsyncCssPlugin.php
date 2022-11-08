<?php

namespace M2Boilerplate\CriticalCss\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\HTTP\Header;
use Magento\Framework\View\Result\Layout;
use Magento\Store\Model\ScopeInterface;

class AsyncCssPlugin extends \Magento\Theme\Controller\Result\AsyncCssPlugin
{
    private ScopeConfigInterface $scopeConfig;
    private Header $httpHeader;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Header $httpHeader
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Header $httpHeader
    ) {
        parent::__construct($scopeConfig);
        $this->scopeConfig = $scopeConfig;
        $this->httpHeader = $httpHeader;
    }

    /**
     * @inheritDoc
     */
    public function afterRenderResult(Layout $subject, Layout $result, ResponseInterface $httpResponse)
    {
        if ($this->canBeProcessed($httpResponse)) {
            return parent::afterRenderResult($subject, $result, $httpResponse);
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function canBeProcessed(ResponseInterface $httpResponse): bool
    {
        // NOTE: validate Critical Css activity Flag
        if (!$this->isCssCriticalEnabled()) {
            return false;
        }

        // NOTE: validate Request, it MUST NOT be initiated by NPM CRITICAL-CSS
        //     check on user-agent value
        if ($this->httpHeader->getHttpUserAgent() === 'got (https://github.com/sindresorhus/got)') {
            return false;
        }

        // NOTE: validate if CriticalCss node includes not-empty content
        $content = (string)$httpResponse->getContent();
        if ($this->isCriticalCssNodeEmpty($content)) {
            return false;
        }

        return true;
    }

    /**
     * NOTE:
     * @see \Magento\Theme\Controller\Result\AsyncCssPlugin::isCssCriticalEnabled
     *
     * Returns information whether css critical path is enabled
     *
     * @return bool
     */
    private function isCssCriticalEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'dev/css/use_css_critical_path',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Validates if STYLE CriticalCss-node exists and is NOT empty
     *
     * @param string $content
     * @return bool
     */
    private function isCriticalCssNodeEmpty(string $content): bool
    {
        $styles = '';
        $styleOpen = '<style';
        $styleClose = '/style>';
        $styleOpenPos = strpos($content, $styleOpen);

        $headClosePos = strpos($content, '</head>');

        while ($styleOpenPos !== false) {
            // NOTE: no need to proceed in case lines on HEAD-node have been processed
            if ($styleOpenPos >= $headClosePos) {
                break;
            }

            $styleClosePos = strpos($content, $styleClose, $styleOpenPos);
            $style = substr($content, $styleOpenPos, $styleClosePos - $styleOpenPos + strlen($styleClose));

            // NOTE: validation of STYLE-node string (tags exactly and node's inner content).
            //     in case match - fetch the styles and filter the string
            if (preg_match('@<style.+data-type=["\']criticalCss["\'](.+)?>(.+)</style>@s', $style, $matches)) {
                $styles = str_replace(
                    ["\n","\r\n","\r"],
                    '',
                    trim((string)($matches[2] ?? null)));
                break;
            }

            // NOTE: remove processed style-node from HTML.
            $content = str_replace($style, '', $content);

            // NOTE: style-node was cut out, search for the next one at its former position.
            $styleOpenPos = strpos($content, $styleOpen, $styleOpenPos);
        }

        return empty($styles);
    }
}

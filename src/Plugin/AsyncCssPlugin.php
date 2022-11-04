<?php

namespace M2Boilerplate\CriticalCss\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\HTTP\Header;
use Magento\Framework\View\Result\Layout;

class AsyncCssPlugin extends \Magento\Theme\Controller\Result\AsyncCssPlugin
{
    private Header $httpHeader;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Header $httpHeader
    ) {
        parent::__construct($scopeConfig);
        $this->httpHeader = $httpHeader;
    }

    /**
     * @inheritDoc
     */
    public function afterRenderResult(Layout $subject, Layout $result, ResponseInterface $httpResponse)
    {
        if ($this->canBeProcessed()) {
            return parent::afterRenderResult($subject, $result, $httpResponse);
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function canBeProcessed(): bool
    {
        // NOTE: validate Request it MUST NOT be initiated by NPM CRITICAL-CSS
        //     check on user-agent value

        return $this->httpHeader->getHttpUserAgent() !== 'got (https://github.com/sindresorhus/got)';
    }
}

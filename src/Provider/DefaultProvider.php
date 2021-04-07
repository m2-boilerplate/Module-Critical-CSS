<?php

namespace M2Boilerplate\CriticalCss\Provider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;

class DefaultProvider implements ProviderInterface
{
    const NAME = 'default';

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var BuilderInterface
     */
    protected $pageLayoutBuilder;

    public function __construct(UrlInterface $url, BuilderInterface $pageLayoutBuilder)
    {
        $this->url = $url;
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }


    public function getUrls(StoreInterface $store): array
    {
        $options = array_keys($this->pageLayoutBuilder->getPageLayoutsConfig()->getOptions());

        $urls = [];
        foreach ($options as $option) {
            $urls[$option] =  $store->getUrl('m2bp/criticalCss/default', ['page_layout' => $option]);
        }
        return $urls;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return PHP_INT_MIN;
    }

    public function getCssIdentifierForRequest(RequestInterface $request, LayoutInterface $layout): ?string
    {
        return $layout->getUpdate()->getPageLayout();
    }


}

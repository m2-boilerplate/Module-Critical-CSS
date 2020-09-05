<?php

namespace M2Boilerplate\CriticalCss\Provider;


use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;

class ContactProvider implements ProviderInterface
{
    const NAME = 'contact';

    /**
     * @var UrlInterface
     */
    protected $url;

    public function __construct(UrlInterface $url)
    {
        $this->url = $url;
    }


    public function getUrls(StoreInterface $store): array
    {
        return [
            'contact_index_index' => $this->url->getUrl('contact'),
        ];
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
        return 1200;
    }

    public function getCssIdentifierForRequest(RequestInterface $request, LayoutInterface $layout): ?string
    {
        if ($request->getModuleName() !== 'contact' || !$request instanceof Http) {
            return null;
        }
        if (
            $request->getFullActionName('_') === 'contact_index_index'
        ) {
            return 'contact_index_index';
        }

        return null;
    }
}
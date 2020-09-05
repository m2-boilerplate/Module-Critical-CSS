<?php

namespace M2Boilerplate\CriticalCss\Provider;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;

class CustomerProvider implements ProviderInterface
{
    const NAME = 'customer';

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
            'customer_account_login' => $this->url->getUrl('customer/account/login'),
            'customer_account_create' => $this->url->getUrl('customer/account/create'),
            'customer_account_forgotpassword' => $this->url->getUrl('customer/account/forgotpassword'),
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
        return 1100;
    }

    public function getCssIdentifierForRequest(RequestInterface $request, LayoutInterface $layout): ?string
    {
        if ($request->getModuleName() !== 'cms' || !$request instanceof Http) {
            return null;
        }

        $actionName = $request->getFullActionName('_');
        $supportedActions = [
            'customer_account_login',
            'customer_account_create',
            'customer_account_forgotpassword'
        ];
        if (in_array($actionName, $supportedActions)) {
            return $actionName;
        }

        return null;
    }

}
<?php

namespace M2Boilerplate\CriticalCss\Plugin;

use M2Boilerplate\CriticalCss\Provider\Container;
use M2Boilerplate\CriticalCss\Service\Identifier;
use M2Boilerplate\CriticalCss\Service\Storage;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;

class CriticalCss
{

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Container
     */
    protected $container;
    /**
     * @var Storage
     */
    protected $storage;
    /**
     * @var Identifier
     */
    protected $identifier;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        LayoutInterface $layout,
        RequestInterface $request,
        Container $container,
        Storage $storage,
        Identifier $identifier,
        StoreManagerInterface $storeManager
    ) {
        $this->layout = $layout;
        $this->request = $request;
        $this->container = $container;
        $this->storage = $storage;
        $this->identifier = $identifier;
        $this->storeManager = $storeManager;
    }

    public function afterGetCriticalCssData(\Magento\Theme\Block\Html\Header\CriticalCss $subject, $result)
    {
        $providers = $this->container->getProviders();
        try {
            $store = $this->storeManager->getStore();
        } catch (NoSuchEntityException $e) {
            return $result;
        }

        foreach ($providers as $provider) {
            if ($identifier = $provider->getCssIdentifierForRequest($this->request, $this->layout)) {
                $identifier = $this->identifier->generateIdentifier($provider, $store, $identifier);
                $css = $this->storage->getCriticalCss($identifier);
                if ($css) {
                    return $css;
                }
            }
        }

        return $result;
    }

}
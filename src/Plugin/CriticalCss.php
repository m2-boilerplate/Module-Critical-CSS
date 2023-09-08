<?php

namespace M2Boilerplate\CriticalCss\Plugin;

use M2Boilerplate\CriticalCss\Console\Command\GenerateCommand;
use M2Boilerplate\CriticalCss\Provider\Container;
use M2Boilerplate\CriticalCss\Service\Identifier;
use M2Boilerplate\CriticalCss\Service\Storage;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\FlagManager;
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

    /**
     * @var FlagManager
     */
    protected $flagManager;

    public function __construct(
        FlagManager $flagManager,
        LayoutInterface $layout,
        RequestInterface $request,
        Container $container,
        Storage $storage,
        Identifier $identifier,
        StoreManagerInterface $storeManager
    ) {
        $this->flagManager = $flagManager;
        $this->layout = $layout;
        $this->request = $request;
        $this->container = $container;
        $this->storage = $storage;
        $this->identifier = $identifier;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Theme\Block\Html\Header\CriticalCss $subject
     * @param $result generated CSS code to be inline injected to page head
     * @return string|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function afterGetCriticalCssData(\Magento\Theme\Block\Html\Header\CriticalCss $subject, $result)
    {
        $result = '';

        $providers = $this->container->getProviders();

        try {
            $store = $this->storeManager->getStore();
        } catch (NoSuchEntityException $e) {
            return $result;
        }

        foreach ($providers as $provider) {
            if ($identifier = $provider->getCssIdentifierForRequest($this->request, $this->layout)) {
                $identifier = $this->identifier->generateIdentifier($provider, $store, $identifier);
                $result = $this->storage->getCriticalCss($identifier);
                if ($result) {
                    break;
                }
            }
        }

        return $result;
    }

}

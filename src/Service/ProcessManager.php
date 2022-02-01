<?php

namespace M2Boilerplate\CriticalCss\Service;

use M2Boilerplate\CriticalCss\Model\ProcessContextFactory;
use M2Boilerplate\CriticalCss\Config\Config;
use M2Boilerplate\CriticalCss\Model\ProcessContext;
use M2Boilerplate\CriticalCss\Provider\Container;
use M2Boilerplate\CriticalCss\Provider\ProviderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ProcessManager
{
    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var CriticalCss
     */
    protected $criticalCssService;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ProcessContextFactory
     */
    protected $contextFactory;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * @var CssProcessor
     */
    protected $cssProcessor;

    public function __construct(
        LoggerInterface $logger,
        Storage $storage,
        ProcessContextFactory $contextFactory,
        Config $config,
        CriticalCss $criticalCssService,
        Emulation $emulation,
        StoreManagerInterface $storeManager,
        Container $container,
        CssProcessor $cssProcessor
    ) {
        $this->emulation = $emulation;
        $this->storeManager = $storeManager;
        $this->container = $container;
        $this->criticalCssService = $criticalCssService;
        $this->config = $config;
        $this->contextFactory = $contextFactory;
        $this->storage = $storage;
        $this->logger = $logger;
        $this->cssProcessor = $cssProcessor;
    }

    /**
     * @param ProcessContext[] $processList
     * @param bool             $deleteOldFiles
     */
    public function executeProcesses(array $processList, bool $deleteOldFiles = false): void
    {

        if ($deleteOldFiles) {
            $this->storage->clean();
        }
        /** @var ProcessContext[] $batch */
        $batch = array_splice($processList, 0, $this->config->getNumberOfParallelProcesses());
        foreach ($batch as $context) {
            $context->getProcess()->start();
            $this->logger->debug(sprintf('[%s|%s] > %s', $context->getProvider()->getName(), $context->getOrigIdentifier(), $context->getProcess()->getCommandLine()));
        }
        while (count($processList) > 0 || count($batch) > 0) {
            foreach ($batch as $key => $context) {
                if (!$context->getProcess()->isRunning()) {
                    try {
                        $this->handleEndedProcess($context);
                    } catch (ProcessFailedException $e) {
                        $this->logger->error($e);
                    }
                    unset($batch[$key]);
                    if (count($processList) > 0) {
                        $newProcess = array_shift($processList);
                        $newProcess->getProcess()->start();
                        $this->logger->debug(sprintf('[%s|%s] - %s', $context->getProvider()->getName(), $context->getOrigIdentifier(), $context->getProcess()->getCommandLine()));
                        $batch[] = $newProcess;
                    }
                }
            }
            usleep(500); // wait for processes to finish
        }

    }

    public function createProcesses(): array
    {
        $processList = [];
        foreach ($this->storeManager->getStores() as $storeId => $store) {
            // Skip store if store is not active
            if (!$store->getIsActive()) continue;
            $this->emulation->startEnvironmentEmulation($storeId,\Magento\Framework\App\Area::AREA_FRONTEND, true);
            $this->storeManager->setCurrentStore($storeId);


            foreach ($this->container->getProviders() as $provider) {
                $processList = array_merge($processList, $this->createProcessesForProvider($provider, $store));
            }
            $this->emulation->stopEnvironmentEmulation();
        }

        return $processList;
    }

    public function createProcessesForProvider(ProviderInterface $provider, StoreInterface $store): array
    {
        $processList = [];
        $urls = $provider->getUrls($store);
        foreach ($urls as $identifier => $url) {
            $this->logger->info(sprintf('[%s:%s|%s] - %s', $store->getCode(), $provider->getName(), $identifier, $url));
            $process = $this->criticalCssService->createCriticalCssProcess(
                $url,
                $this->config->getDimensions(),
                $this->config->getCriticalBinary(),
                $this->config->getUsername(),
                $this->config->getPassword()
            );
            $context = $this->contextFactory->create([
                'process' => $process,
                'store' => $store,
                'provider' => $provider,
                'identifier' => $identifier
            ]);
            $processList[] = $context;
        }
        return $processList;
    }

    protected function handleEndedProcess(ProcessContext $context)
    {
        $process = $context->getProcess();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $criticalCss = $process->getOutput();
        $this->storage->saveCriticalCss($context->getIdentifier(), $this->cssProcessor->process($criticalCss));
        $size = $this->storage->getFileSize($context->getIdentifier());
        if (!$size) {
            $size = '?';
        }
        $this->logger->info(
            sprintf('[%s:%s|%s] Finished: %s.css (%s bytes)',
                $context->getStore()->getCode(),
                $context->getProvider()->getName(),
                $context->getOrigIdentifier(),
                $context->getIdentifier(),
                $size
            )
        );
    }

}

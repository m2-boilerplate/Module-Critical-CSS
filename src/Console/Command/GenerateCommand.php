<?php

namespace M2Boilerplate\CriticalCss\Console\Command;

use M2Boilerplate\CriticalCss\Config\Config;
use M2Boilerplate\CriticalCss\Logger\Handler\ConsoleHandlerFactory;
use M2Boilerplate\CriticalCss\Service\CriticalCss;
use M2Boilerplate\CriticalCss\Service\ProcessManager;
use M2Boilerplate\CriticalCss\Service\ProcessManagerFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\App\Cache\Manager;

class GenerateCommand extends Command
{
    /**
     * @var ProcessManagerFactory
     */
    protected $processManagerFactory;
    /**
     * @var ConsoleHandlerFactory
     */
    protected $consoleHandlerFactory;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var CriticalCss
     */
    protected $criticalCssService;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var FlagManager
     */
    protected $flagManager;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var Manager
     */
    protected $cacheManager;

    public function __construct(
        FlagManager $flagManager,
        Config $config,
        CriticalCss $criticalCssService,
        ObjectManagerInterface $objectManager,
        ConsoleHandlerFactory $consoleHandlerFactory,
        ProcessManagerFactory $processManagerFactory,
        State $state,
        WriterInterface $configWriter,
        Manager $cacheManager,
        ?string $name = null
    )
    {
        parent::__construct($name);
        $this->flagManager = $flagManager;
        $this->processManagerFactory = $processManagerFactory;
        $this->consoleHandlerFactory = $consoleHandlerFactory;
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->criticalCssService = $criticalCssService;
        $this->state = $state;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
    }


    protected function configure()
    {
        $this->setName('m2bp:critical-css:generate');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

            // TODO: decide whether cache flushing is really required. temporally commented.
            //$this->cacheManager->flush($this->cacheManager->getAvailableTypes());

            $this->criticalCssService->test($this->config->getCriticalBinary());

            $consoleHandler = $this->consoleHandlerFactory->create(['output' => $output]);

            $logger = $this->objectManager->create('M2Boilerplate\CriticalCss\Logger\Console', ['handlers' => ['console' => $consoleHandler]]);

            /** @var ProcessManager $processManager */
            $processManager = $this->processManagerFactory->create(['logger' => $logger]);


            $output->writeln('<warning>\'Use CSS critical path\' status ' . ($this->config->isEnabled() ? 'Enabled' : 'Disabled') . '</warning>');
            $output->writeln("<info>-----------------------------------------</info>");
            $output->writeln('<info>Critical Command Configured Options</info>');
            $output->writeln("<info>-----------------------------------------</info>");
            $output->writeln('<comment>Screen Dimensions: ' . implode('', $this->config->getDimensions()) . '</comment>');
            $output->writeln('<comment>Force Include Css Selectors: ' . implode('', $this->config->getForceIncludeCssSelectors()) . '</comment>');

            $output->writeln('<comment>HTTP Auth Username: ' .  $this->config->getUsername() . '</comment>');
            $output->writeln('<comment>HTTP Auth Password: ' .  $this->config->getPassword() . '</comment>');

            $output->writeln("<info>-----------------------------------------</info>");
            $output->writeln('<info>Gathering URLs...</info>');
            $output->writeln("<info>-----------------------------------------</info>");
            $processes = $processManager->createProcesses();
            $output->writeln("<info>-----------------------------------------</info>");
            $output->writeln('<info>Generating Critical CSS for ' . count($processes) . ' URLs...</info>');
            $output->writeln("<info>-----------------------------------------</info>");
            $processManager->executeProcesses($processes, true);

            // TODO: decide whether cache flushing is really required. temporally commented.
            // $this->cacheManager->flush($this->cacheManager->getAvailableTypes());

        } catch (\Throwable $e) {
            throw $e;
        }
        return 0;
    }
}

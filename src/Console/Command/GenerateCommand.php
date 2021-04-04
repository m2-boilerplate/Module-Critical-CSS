<?php

namespace M2Boilerplate\CriticalCss\Console\Command;

use M2Boilerplate\CriticalCss\Config\Config;
use M2Boilerplate\CriticalCss\Logger\Handler\ConsoleHandlerFactory;
use M2Boilerplate\CriticalCss\Service\CriticalCss;
use M2Boilerplate\CriticalCss\Service\ProcessManager;
use M2Boilerplate\CriticalCss\Service\ProcessManagerFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\FlagManager;

class GenerateCommand extends Command
{

    const IS_CRITICAL_CSS_GENERATION_RUNNING = 'm2bp_critical_css_is_running';

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

    protected $flagManager;

    public function __construct(
        FlagManager $flagManager,
        Config $config,
        CriticalCss $criticalCssService,
        ObjectManagerInterface $objectManager,
        ConsoleHandlerFactory $consoleHandlerFactory,
        ProcessManagerFactory $processManagerFactory,
        State $state,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->flagManager = $flagManager;
        $this->processManagerFactory = $processManagerFactory;
        $this->consoleHandlerFactory = $consoleHandlerFactory;
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->criticalCssService = $criticalCssService;
        $this->state = $state;
    }


    protected function configure()
    {
        $this->setName('m2bp:critical-css:generate');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

            if (!$this->isEnabled()) {
                $output->writeln('<error>Critical CSS is not enabled please enable it using: bin/magento config:set dev/css/use_css_critical_path 1</error>');
                return -1;
            }
            $this->criticalCssService->test($this->config->getCriticalBinary());
            $this->flagManager->saveFlag(self::IS_CRITICAL_CSS_GENERATION_RUNNING, true);
            $consoleHandler = $this->consoleHandlerFactory->create(['output' => $output]);
            $logger = $this->objectManager->create('M2Boilerplate\CriticalCss\Logger\Console', ['handlers' => ['console' => $consoleHandler]]);
            $output->writeln('<info>Generating Critical CSS</info>');



            /** @var ProcessManager $processManager */
            $processManager = $this->processManagerFactory->create(['logger' => $logger]);
            $output->writeln('<info>Gathering URLs...</info>');
            $processes = $processManager->createProcesses();
            $output->writeln('<info>Generating Critical CSS for '.count($processes).' URLs...</info>');
            $processManager->executeProcesses($processes, true);
            $this->flagManager->deleteFlag(self::IS_CRITICAL_CSS_GENERATION_RUNNING);
        } catch (\Throwable $e) {
            $this->flagManager->deleteFlag(self::IS_CRITICAL_CSS_GENERATION_RUNNING);
            throw $e;
        }
        return 0;
    }


}

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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\FlagManager;

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

    protected $flagManager;

    protected $configWriter;

    public function __construct(
        FlagManager $flagManager,
        Config $config,
        CriticalCss $criticalCssService,
        ObjectManagerInterface $objectManager,
        ConsoleHandlerFactory $consoleHandlerFactory,
        ProcessManagerFactory $processManagerFactory,
        State $state,
        WriterInterface $configWriter,
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

            $output->writeln('<info>Disabling ' . Config::CONFIG_PATH_ENABLED . ' while collecting css...</info>');
            $this->getApplication()->find("config:set")->run(new ArrayInput(["path" => Config::CONFIG_PATH_ENABLED, "value" => "0", "--lock-env" => 1]), $output);
            $this->getApplication()->find("app:config:import")->run(new ArrayInput([]), $output);
            $this->getApplication()->find("cache:flush")->run(new ArrayInput([]), $output);

            $this->criticalCssService->test($this->config->getCriticalBinary());
            $consoleHandler = $this->consoleHandlerFactory->create(['output' => $output]);
            $logger = $this->objectManager->create('M2Boilerplate\CriticalCss\Logger\Console', ['handlers' => ['console' => $consoleHandler]]);
            $output->writeln('<info>Generating Critical CSS</info>');

            /** @var ProcessManager $processManager */
            $processManager = $this->processManagerFactory->create(['logger' => $logger]);
            $output->writeln('<info>Gathering URLs...</info>');
            $processes = $processManager->createProcesses();
            $output->writeln('<info>Generating Critical CSS for ' . count($processes) . ' URLs...</info>');
            $processManager->executeProcesses($processes, true);

            $output->writeln('<info>Enabling ' . Config::CONFIG_PATH_ENABLED . '...</info>');
            $this->getApplication()->find("config:set")->run(new ArrayInput(["path" => Config::CONFIG_PATH_ENABLED, "value" => "1", "--lock-env" => 1]), $output);
            $this->getApplication()->find("app:config:import")->run(new ArrayInput([]), $output);
            $this->getApplication()->find("cache:flush")->run(new ArrayInput([]), $output);

        } catch (\Throwable $e) {
            throw $e;
        }
        return 0;
    }


}

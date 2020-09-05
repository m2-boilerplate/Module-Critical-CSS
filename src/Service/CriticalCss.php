<?php

namespace M2Boilerplate\CriticalCss\Service;


use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessFactory;

class CriticalCss
{
    /**
     * @var ProcessFactory
     */
    protected $processFactory;

    public function __construct(ProcessFactory $processFactory)
    {

        $this->processFactory = $processFactory;
    }


    public function createCriticalCssProcess(
        string $url,
        array $dimensions,
        string $criticalBinary = 'critical',
        ?string $username = null,
        ?string $password = null
    ) {
        $command = [
            $criticalBinary,
            $url
        ];
        foreach ($dimensions as $dimension) {
            $command[] = '--dimensions';
            $command[] = $dimension;
        }

        if ($username && $password) {
            $command[] = '--user';
            $command[] = $username;
            $command[] = '--pass';
            $command[] = $password;
        }

        /** @var Process $process */
        $process = $this->processFactory->create(['command' => $command]);

        return $process;
    }

    public function getVersion(string $criticalBinary = 'critical'): string
    {
        $process = $this->processFactory->create(['command' => [$criticalBinary, '--version']]);
        $process->mustRun();
        return trim($process->getOutput());
    }

    public function test(string $criticalBinary = 'critical'): void
    {
        $version = $this->getVersion($criticalBinary);
        if (version_compare($version, '2.0.3', '<')) {
            throw new \RuntimeException('critical version 2.0.3 is the minimum requirement, got: '.$version);
        }
    }

}
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
        array $forceIncludeCssSelectors,
        ?string $username = null,
        ?string $password = null
    ) {
        $command = [
            $criticalBinary,
            $url
        ];

        foreach ($forceIncludeCssSelectors as $selector) {
            $command[] = '--penthouse-forceInclude';
            $command[] = $selector;
        }

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

        $command[] = '--strict';
        $command[] = '--no-request-https.rejectUnauthorized';

        $command[] = '--ignore-atrule';
        $command[] = '@font-face';

        //$command[] = '--penthouse-blockJSRequests';
        //$command[] = 'true';

        /** @var Process $process */
        $process = $this->processFactory->create(['command' => $command, 'commandline' => $command]);

        return $process;
    }

    public function getVersion(string $criticalBinary = 'critical'): string
    {
        $command = [$criticalBinary, '--version'];
        $process = $this->processFactory->create(['command' => $command, 'commandline' => $command]);
        $process->mustRun();
        return trim($process->getOutput());
    }

    public function test(string $criticalBinary = 'critical'): void
    {
        $version = $this->getVersion($criticalBinary);
        if (version_compare($version, '2.0.6', '<')) {
            throw new \RuntimeException('critical version 2.0.6 is the minimum requirement, got: '.$version);
        }
    }

}

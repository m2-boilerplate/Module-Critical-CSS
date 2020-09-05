<?php

namespace M2Boilerplate\CriticalCss\Model;

use M2Boilerplate\CriticalCss\Provider\ProviderInterface;
use M2Boilerplate\CriticalCss\Service\Identifier;
use Magento\Store\Api\Data\StoreInterface;
use Symfony\Component\Process\Process;

class ProcessContext
{
    /**
     * @var Process
     */
    protected $process;
    /**
     * @var ProviderInterface
     */
    protected $provider;
    /**
     * @var StoreInterface
     */
    protected $store;
    /**
     * @var string
     */
    protected $identifier;
    /**
     * @var Identifier
     */
    protected $identifierService;

    public function __construct(
        Process $process,
        ProviderInterface $provider,
        StoreInterface $store,
        Identifier $identifierService,
        string $identifier
    ) {

        $this->process = $process;
        $this->provider = $provider;
        $this->store = $store;
        $this->identifier = $identifier;
        $this->identifierService = $identifierService;
    }

    /**
     * @return StoreInterface
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    public function getOrigIdentifier()
    {
        return $this->identifier;
    }

    public function getIdentifier()
    {
        return $this->identifierService->generateIdentifier(
            $this->provider,
            $this->store,
            $this->identifier
        );
    }

}
<?php

namespace M2Boilerplate\CriticalCss\Provider;

class Container
{

    /**
     * @var ProviderInterface[]
     */
    protected $providers = [];

    public function __construct(array $providers = [])
    {
        foreach ($providers as $provider) {
            if (!$provider instanceof ProviderInterface) {
                continue;
            }
            $this->addProvider($provider);
        }
    }

    /**
     * @return ProviderInterface[]
     */
    public function getProviders(): array
    {
        usort($this->providers, function (ProviderInterface $a, ProviderInterface $b) {
            if ($a->getPriority() === $b->getPriority()) {
                return 0;
            }
            return ($a->getPriority() < $b->getPriority()) ? 1 : -1;
        });
        return $this->providers;
    }

    /**
     * @param ProviderInterface $provider
     */
    public function addProvider(ProviderInterface $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    /**
     * @param string $name
     *
     * @return ProviderInterface|null
     */
    public function getProvider(string $name): ?ProviderInterface
    {
        return isset($this->providers[$name]) ? $this->providers[$name] : null;
    }


}
<?php

namespace App\Pos;

/**
 * @class PosFactory
 * @package App\Pos
 */
class PosFactory
{
    /**
     * @var array
     */
    private $providersNames;

    /**
     * @var array
     */
    private $providers;

    /**
     * @param array $providers
     */
    public function __construct(array $providers)
    {
        foreach ($providers as $provider) {
            $providerName = $provider->getName();

            $this->providersNames[] = $providerName;
            $this->providers[$providerName] = $provider;
        }
    }

    /**
     * @return string[]
     */
    public function getProvidersNames(): array
    {
        return $this->providersNames;
    }

    /**
     * @return Providers\PosInterface[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @param string $name
     * @return Providers\PosInterface
     * @throws \Exception
     */
    public function getProviderByName(string $name): Providers\PosInterface
    {
        if (!isset($this->providers[$name])) {
            throw new \Exception(sprintf('Provider "%s" not found.', $name));
        }

        return $this->providers[$name];
    }
}

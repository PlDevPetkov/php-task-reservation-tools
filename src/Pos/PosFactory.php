<?php

namespace App\Pos;

/**
 * @class PosFactory
 * @package App\Pos
 */
class PosFactory
{
    /**
     * @var Providers\PosInterface[]
     */
    private $providers;

    /**
     * @param Providers\PosInterface[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return Providers\PosInterface[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}

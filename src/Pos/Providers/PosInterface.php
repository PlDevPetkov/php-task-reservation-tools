<?php

namespace App\Pos\Providers;

/**
 * @interface PosInterface
 * @package App\Pos\Providers
 */
interface PosInterface
{
    /**
     * @param \DateTime|null $fromDate
     * @return int
     */
    public function synchronizeOrders(\DateTime $fromDate = null): int;

    /**
     * @return string
     */
    public function getName(): string;
}

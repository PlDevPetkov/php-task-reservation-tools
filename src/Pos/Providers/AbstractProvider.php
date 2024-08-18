<?php

namespace App\Pos\Providers;

use App\Entity\Orders;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

/**
 * @class AbstractProvider
 * @package App\Pos\Providers
 */
abstract class AbstractProvider implements PosInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(array $config, EntityManagerInterface $entityManager)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
    }

    /**
     * @param DateTime|null $fromDate
     * @return int
     * @throws \Exception
     */
    public function synchronizeOrders(DateTime $fromDate = null): int
    {
        $orders = $this->retrieveOrders($fromDate);
        $syncedOrdersCount = 0;

        foreach ($orders as $order) {
            $orderModel = new Orders();
            $orderModel->setProviderId($order->provider_id);
            $orderModel->setProviderName($this->getName());
            $orderModel->setReservationId($order->reservation_id);
            $orderModel->setReservationDetails(json_encode($order));
            $orderModel->setCreatedAt(new DateTime());
            $orderModel->setUpdatedAt(new DateTime());

            $this->entityManager->persist($orderModel);
            $this->entityManager->flush();

            $syncedOrdersCount++;
        }

        return $syncedOrdersCount;
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @param DateTime|null $fromDate
     * @return array
     * @throws \Exception
     */
    abstract protected function retrieveOrders($fromDate): array;
}

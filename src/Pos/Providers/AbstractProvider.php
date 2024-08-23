<?php

namespace App\Pos\Providers;

use App\Entity\Orders;
use App\Pos\Order;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @class AbstractProvider
 * @package App\Pos\Providers
 */
abstract class AbstractProvider implements PosInterface
{
    const ORDERS_BATCH_SIZE = 100;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param array $config
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     */
    public function __construct(array $config, EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
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

        foreach ($orders as $index => $order) {
            $orderModel = new Orders();
            $orderModel->setProviderName($this->getName());
            $orderModel->setProviderId($order->getProviderId());
            $orderModel->setReservationId($order->getReservationId());
            $orderModel->setReservationDetails($this->serializer->serialize($order, 'json'));
            $orderModel->setCreatedAt(new DateTime());
            $orderModel->setUpdatedAt(new DateTime());

            $this->entityManager->persist($orderModel);
            $syncedOrdersCount++;

            if (($index + 1) % self::ORDERS_BATCH_SIZE === 0) {
                $this->flushAndClear();
            }
        }

        $this->flushAndClear();

        return $syncedOrdersCount;
    }

    /**
     * @return void
     */
    private function flushAndClear(): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @param DateTime|null $fromDate
     * @return Order[]
     * @throws \Exception
     */
    abstract protected function retrieveOrders($fromDate): array;
}

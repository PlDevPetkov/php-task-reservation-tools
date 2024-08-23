<?php

namespace App\Tests\Integration\Pos\Providers;

use App\Entity\Orders;
use App\Pos\Providers\Barsy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @class BarsyTest
 * @package App\Tests\Integration\Pos\Providers
 */
class BarsyTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SerializerInterface  */
    private $serializer;

    /** @var Barsy  */
    private $barsy;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->serializer = $container->get(SerializerInterface::class);

        $config = [
            'host' => $_ENV['BARSY_HOST'] ?? 'default_host',
            'user' => $_ENV['BARSY_USER'] ?? 'default_user',
            'password' => $_ENV['BARSY_PASSWORD'] ?? 'default_password',
        ];
        $this->barsy = new Barsy($config, $this->entityManager, $this->serializer);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testSynchronizeEmptyOrders(): void
    {
        $fromDate = new \DateTime('-1 day');
        $resultCount = $this->barsy->synchronizeOrders($fromDate);

        $this->assertEquals(0, $resultCount);

        $orderRepo = $this->entityManager->getRepository(Orders::class);
        $orders = $orderRepo->findAll();

        $this->assertEmpty($orders);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testSynchronizeOrders(): void
    {
        $fromDate = new \DateTime('-2 year');
        $resultCount = $this->barsy->synchronizeOrders($fromDate);

        $this->assertEquals(6, $resultCount);

        $orderRepo = $this->entityManager->getRepository(Orders::class);
        $orders = $orderRepo->findAll();

        $this->assertNotEmpty($orders);

        /** @var Orders $order */
        foreach ($orders as $order) {
            $this->assertNotNull($order->getProviderId());
            $this->assertNotNull($order->getProviderName());
            $this->assertNotNull($order->getReservationId());
            $this->assertNotNull($order->getReservationDetails());
        }
    }
}

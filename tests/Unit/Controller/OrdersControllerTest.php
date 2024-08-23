<?php

namespace App\Tests\Unit\Controller;

use App\Controller\OrdersController;
use App\Entity\Orders;
use App\Pos\Providers\Barsy;
use App\Pos\Providers\Rkeeper;
use App\Repository\OrdersRepository;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Event\ItemsEvent;
use Knp\Component\Pager\Event\PaginationEvent;
use Knp\Component\Pager\Pagination\SlidingPagination;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\ArgumentAccess\ArgumentAccessInterface;
use Knp\Component\Pager\Paginator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @class OrdersControllerTest
 * @package App\Tests\Unit\Controller
 */
class OrdersControllerTest extends TestCase
{
    /**
     * @return void
     */
    public function testOrders(): void
    {
        $ordersRepository = $this->createMock(OrdersRepository::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $argumentAccess = $this->createMock(ArgumentAccessInterface::class);

        $ordersRepository->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener('knp_pager.items', function (ItemsEvent $event) {
            $orders = [
                $this->createOrderMock(1, 10, Barsy::BARSY_PROVIDER_NAME, 100),
                $this->createOrderMock(2, 20, Rkeeper::RKEEPER_PROVIDER_NAME, 200)
            ];

            $event->items = $orders;
            $event->count = count($orders);
            $event->stopPropagation();
        });

        $eventDispatcher->addListener('knp_pager.pagination', function (PaginationEvent $event) {
            $event->setPagination(new SlidingPagination());
            $event->stopPropagation();
        });

        $paginator = new Paginator($eventDispatcher, $argumentAccess);
        $request = new Request(['page' => 1, 'limit' => 10]);

        $controller = new OrdersController($paginator, $ordersRepository);
        $response = $controller->orders($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('content-type'));

        $content = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('items', $content);
        $this->assertArrayHasKey('meta', $content);

        $this->assertCount(2, $content['items']);

        $this->assertEquals(1, $content['items'][0]['id']);
        $this->assertEquals(10, $content['items'][0]['provider_id']);
        $this->assertEquals(Barsy::BARSY_PROVIDER_NAME, $content['items'][0]['provider_name']);
        $this->assertEquals(100, $content['items'][0]['reservation_id']);

        $this->assertEquals(2, $content['items'][1]['id']);
        $this->assertEquals(20, $content['items'][1]['provider_id']);
        $this->assertEquals(Rkeeper::RKEEPER_PROVIDER_NAME, $content['items'][1]['provider_name']);
        $this->assertEquals(200, $content['items'][1]['reservation_id']);

        $this->assertEquals(2, $content['meta']['total']);
        $this->assertEquals(1, $content['meta']['page']);
        $this->assertEquals(10, $content['meta']['limit']);
        $this->assertEquals(1, $content['meta']['pages']);
    }

    /**
     * @param int $id
     * @param int $providerId
     * @param string $providerName
     * @param int $reservationId
     * @return Orders|MockObject
     */
    private function createOrderMock(int $id, int $providerId, string $providerName, int $reservationId)
    {
        $orderMock = $this->createMock(Orders::class);

        $orderMock->method('getId')->willReturn($id);
        $orderMock->method('getProviderId')->willReturn($providerId);
        $orderMock->method('getProviderName')->willReturn($providerName);
        $orderMock->method('getReservationId')->willReturn($reservationId);
        $orderMock->method('getReservationDetails')->willReturn(json_encode(['detail' => 'details_' . $id]));
        $orderMock->method('getCreatedAt')->willReturn(new \DateTime());
        $orderMock->method('getUpdatedAt')->willReturn(new \DateTime());

        return $orderMock;
    }
}

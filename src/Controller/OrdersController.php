<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Repository\OrdersRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @class OrdersController
 * @package App\Controller
 */
class OrdersController
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var OrdersRepository
     */
    private $orderRepository;

    /**
     * @param PaginatorInterface $paginator
     * @param OrdersRepository $orderRepository
     */
    public function __construct(PaginatorInterface $paginator, OrdersRepository $orderRepository)
    {
        $this->paginator = $paginator;
        $this->orderRepository = $orderRepository;
    }

    #[Route('/api/orders', name: 'orders')]
    public function orders(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $queryBuilder = $this->orderRepository->createQueryBuilder('o');
        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            $limit
        );

        $mappedItems = [];
        /** @var Orders $order */
        foreach ($pagination->getItems() as $order) {
            $mappedItems[] = [
                'id' => $order->getId(),
                'provider_id' => $order->getProviderId(),
                'provider_name' => $order->getProviderName(),
                'reservation_id' => $order->getReservationId(),
                'reservation_details' => json_decode($order->getReservationDetails()),
                'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $order->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        $totalItems = $pagination->getTotalItemCount();

        $result = [
            'items' => $mappedItems,
            'meta' => [
                'total' => $pagination->getTotalItemCount(),
                'page' => $pagination->getCurrentPageNumber(),
                'limit' => $pagination->getItemNumberPerPage(),
                'pages' => (int) ceil($totalItems / $limit)
            ]
        ];

        return new Response(
            json_encode($result),
            headers: ['content-type' => 'application/json']
        );
    }
}

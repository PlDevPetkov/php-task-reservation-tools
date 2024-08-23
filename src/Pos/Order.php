<?php

namespace App\Pos;

/**
 * @class Order
 * @package App\Pos
 */
class Order
{
    /**
     * @var
     */
    private $providerId;

    /**
     * @var int
     */
    private $reservationId;

    /**
     * @var string
     */
    private $orderDetails;

    /**
     * @param int $providerId
     * @return Order
     */
    public function setProviderId(int $providerId): Order
    {
        $this->providerId = $providerId;
        return $this;
    }

    /**
     * @param int $reservationId
     * @return Order
     */
    public function setReservationId(int $reservationId): Order
    {
        $this->reservationId = $reservationId;
        return $this;
    }

    /**
     * @param mixed $orderDetails
     * return Order
     */
    public function setOrderDetails($orderDetails): Order
    {
        $this->orderDetails = json_encode($orderDetails);
        return $this;
    }

    /**
     * @return int
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * @return int
     */
    public function getReservationId()
    {
        return $this->reservationId;
    }

    /**
     * @return mixed
     */
    public function getOrderDetails()
    {
        return json_decode($this->orderDetails);
    }
}

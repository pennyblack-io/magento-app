<?php

namespace PennyBlack\App\Mapper;

use Magento\Sales\Model\Order;
use PennyBlack\Model\Order as PennyBlackOrder;

class OrderMapper
{
    private CustomerMapper $customerMapper;
    private OrderDetailsMapper $orderDetailsMapper;

    public function __construct(CustomerMapper $customerMapper, OrderDetailsMapper $orderDetailsMapper)
    {
        $this->customerMapper = $customerMapper;
        $this->orderDetailsMapper = $orderDetailsMapper;
    }

    public function map(Order $order): PennyBlackOrder
    {
        return PennyBlackOrder::fromValues(
            $order->getId(),
            $order->getIncrementId(),
            $order->getCreatedAt(),
            $this->customerMapper->map($order),
            $this->orderDetailsMapper->map($order)
        );
    }
}

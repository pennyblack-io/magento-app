<?php

namespace PennyBlack\App\Provider;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;

class SkusProvider
{
    public function get(Order $order): array
    {
        return array_map(fn(OrderItemInterface $item): string => $item->getSku(), $order->getItems());
    }
}

<?php

namespace PennyBlack\App\Provider;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;

class ProductTitlesProvider
{
    public function get(Order $order): array
    {
        return array_map(fn(OrderItemInterface $item): string => $item->getName(), $order->getItems());
    }
}

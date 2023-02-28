<?php

namespace PennyBlack\App\Provider;

use Magento\Sales\Model\Order;

class ProductTitlesProvider
{
    public function get(Order $order): array
    {
        return array_map(fn($item): string => $item->getName(), $order->getItems());
    }
}

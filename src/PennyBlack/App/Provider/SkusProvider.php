<?php

namespace PennyBlack\App\Provider;

use Magento\Sales\Model\Order;

class SkusProvider
{
    public function get(Order $order): array
    {
        return array_map(fn($item): string => $item->getSku(), $order->getItems());
    }
}

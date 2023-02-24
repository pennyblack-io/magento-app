<?php

namespace PennyBlack\App\Provider;

use Magento\Sales\Model\Order;

class ProductTitlesProvider
{
    public function get(Order $order): array
    {
        $titles = [];
        foreach ($order->getItems() as $item) {
            $titles[] = $item->getName();
        }

        return $titles;
    }
}

<?php

namespace PennyBlack\App\Provider;

use Magento\Sales\Model\Order;

class SkusProvider
{
    public function get(Order $order): array
    {
        $skus = [];
        foreach ($order->getItems() as $item) {
            $skus[] = $item->getSku();
        }

        return $skus;
    }
}

<?php

namespace PennyBlack\App\Test\Unit\Provider;

use Magento\Sales\Model\Order;
use PennyBlack\App\Provider\SkusProvider;
use PHPUnit\Framework\TestCase;

class SkusProviderTest extends TestCase
{
    public function testItProvidesAnArrayOfSkus()
    {
        $mockItem1 = $this->createMock(Order\Item::class);
        $mockItem2 = $this->createMock(Order\Item::class);

        $mockItem1->method('getSku')->willReturn('test-product-1');
        $mockItem2->method('getSku')->willReturn('test-product-2');

        $mockOrder = $this->createMock(Order::class);
        $mockOrder->expects($this->once())->method('getItems')->willReturn([$mockItem1, $mockItem2]);

        $skuProvider = new SkusProvider();

        $res = $skuProvider->get($mockOrder);

        $this->assertEquals(['test-product-1', 'test-product-2'], $res);
    }

    public function testItProvidesAnEmptyArrayIfNoProductsGiven()
    {
        $mockOrder = $this->createMock(Order::class);
        $mockOrder->expects($this->once())->method('getItems')->willReturn([]);

        $skuProvider = new SkusProvider();

        $res = $skuProvider->get($mockOrder);

        $this->assertEquals([], $res);
    }
}

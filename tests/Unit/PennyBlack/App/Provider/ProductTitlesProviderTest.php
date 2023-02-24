<?php

namespace PennyBlack\PennyBlack\tests\Unit\PennyBlack\App\Provider;

use Magento\Sales\Model\Order;
use PennyBlack\App\Provider\ProductTitlesProvider;
use PHPUnit\Framework\TestCase;

class ProductTitlesProviderTest extends TestCase
{
    public function testItProvidesAnArrayOfProductTitles()
    {
        $mockItem1 = $this->createMock(Order\Item::class);
        $mockItem2 = $this->createMock(Order\Item::class);

        $mockItem1->method('getName')->willReturn('Macbook Pro 13.3');
        $mockItem2->method('getName')->willReturn('HP Envy');

        $mockOrder = $this->createMock(Order::class);
        $mockOrder->expects($this->once())->method('getItems')->willReturn([$mockItem1, $mockItem2]);

        $productTitlesProvider = new ProductTitlesProvider();

        $res = $productTitlesProvider->get($mockOrder);

        $this->assertEquals(['Macbook Pro 13.3', 'HP Envy'], $res);
    }

    public function testItProvidesAnEmptyArrayIfNoProductsGiven()
    {
        $mockOrder = $this->createMock(Order::class);
        $mockOrder->expects($this->once())->method('getItems')->willReturn([]);

        $productTitlesProvider = new ProductTitlesProvider();

        $res = $productTitlesProvider->get($mockOrder);

        $this->assertEquals([], $res);
    }
}
<?php

namespace PennyBlack\App\Test\Unit\Mapper;

use Magento\Sales\Model\Order;
use PennyBlack\App\Mapper\CustomerMapper;
use PennyBlack\App\Mapper\OrderDetailsMapper;
use PennyBlack\App\Mapper\OrderMapper;
use PennyBlack\Model\Customer as PennyBlackCustomer;
use PennyBlack\Model\Order as PennyBlackOrder;
use PennyBlack\Model\OrderDetails;
use PHPUnit\Framework\TestCase;

class OrderMapperTest extends TestCase
{
    private $mockCustomerMapper;
    private $mockOrderDetailsMapper;

    public function setUp(): void
    {
        $this->mockCustomerMapper = $this->createMock(CustomerMapper::class);
        $this->mockOrderDetailsMapper = $this->createMock(OrderDetailsMapper::class);
    }

    public function testItMapsToAPennyBlackOrder()
    {
        $mockOrder = $this->createMock(Order::class);
        $mockOrder->method('getId')->willReturn(1);
        $mockOrder->method('getIncrementId')->willReturn('0000001');
        $mockOrder->method('getCreatedAt')->willReturn('2023-02-27 10:49:25');

        $customer = PennyBlackCustomer::fromValues(
            1,
            'Tim',
            'Apple',
            'tim@apple.com',
            'en_EN',
            '1',
            21,
            [],
            462.76
        );

        $orderDetails = OrderDetails::fromValues(
            '0000001',
            55.99,
            1,
            'GB',
            'SE1 3JW',
            'London',
            'GB',
            'SE1 3JW',
            'London',
            'GBP',
            'A lovely gift message',
            ['WB004'],
            ['Shoulder bag'],
            ['10PERCENTOFF']
        );

        $this->mockCustomerMapper->method('map')->with($mockOrder)->willReturn($customer);
        $this->mockOrderDetailsMapper->method('map')->with($mockOrder)->willReturn($orderDetails);

        $mapper = new OrderMapper($this->mockCustomerMapper, $this->mockOrderDetailsMapper);

        $exp = PennyBlackOrder::fromValues(
            1,
            '0000001',
            '2023-02-27 10:49:25',
            $customer,
            $orderDetails
        );

        $this->assertEquals($exp->toArray(), $mapper->map($mockOrder)->toArray());
    }
}

<?php

namespace PennyBlack\App\Test\Unit\Mapper;

use Magento\Directory\Model\Currency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Model\OrderRepository as GiftMessageRepository;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use PennyBlack\App\Mapper\OrderDetailsMapper;
use PennyBlack\App\Provider\ProductTitlesProvider;
use PennyBlack\App\Provider\SkusProvider;
use PennyBlack\Model\OrderDetails;
use PHPUnit\Framework\TestCase;

class OrderDetailsMapperTest extends TestCase
{
    private $mockSkusProvider;
    private $mockProductTitlesProvider;
    private $mockGiftMessageRepository;

    private $mockOrder;

    public function setUp(): void
    {
        $this->mockSkusProvider = $this->createMock(SkusProvider::class);
        $this->mockProductTitlesProvider = $this->createMock(ProductTitlesProvider::class);
        $this->mockGiftMessageRepository = $this->createMock(GiftMessageRepository::class);

        $this->mockSkusProvider->method('get')->willReturn(['WB004']);
        $this->mockProductTitlesProvider->method('get')->willReturn(['Shoulder bag']);

        $mockBillingAddress = $this->createMock(OrderAddressInterface::class);
        $mockBillingAddress->method('getCountryId')->willReturn('GB');
        $mockBillingAddress->method('getPostcode')->willReturn('SE1 3JW');
        $mockBillingAddress->method('getCity')->willReturn('London');

        $mockShippingAddress = $this->createMock(OrderAddressInterface::class);
        $mockShippingAddress->method('getCountryId')->willReturn('GB');
        $mockShippingAddress->method('getPostcode')->willReturn('SE1 3JW');
        $mockShippingAddress->method('getCity')->willReturn('London');

        $mockCurrency = $this->createMock(Currency::class);
        $mockCurrency->method('getCurrencyCode')->willReturn('GBP');

        $this->mockOrder = $this->createMock(Order::class);
        $mockOrderItem = $this->createMock(Order\Item::class);

        $this->mockOrder->method('getId')->willReturn(1);
        $this->mockOrder->method('getBillingAddress')->willReturn($mockBillingAddress);
        $this->mockOrder->method('getShippingAddress')->willReturn($mockShippingAddress);
        $this->mockOrder->method('getIncrementId')->willReturn('0000001');
        $this->mockOrder->method('getBaseGrandTotal')->willReturn(55.99);
        $this->mockOrder->method('getItems')->willReturn([$mockOrderItem]);
        $this->mockOrder->method('getOrderCurrency')->willReturn($mockCurrency);
        $this->mockOrder->method('getCouponCode')->willReturn('10PERCENTOFF');
    }

    public function testItMapsOrderDataToPennyBlackOrderDetailsWithAGiftMessage()
    {
        $mockGiftMessage = $this->createMock(MessageInterface::class);
        $mockGiftMessage->method('getMessage')->willReturn('A lovely gift message');
        $this->mockGiftMessageRepository->method('get')->with(1)->willReturn($mockGiftMessage);

        $mapper = new OrderDetailsMapper(
            $this->mockSkusProvider,
            $this->mockProductTitlesProvider,
            $this->mockGiftMessageRepository
        );

        $exp = OrderDetails::fromValues(
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

        $this->assertEquals($exp->toArray(), $mapper->map($this->mockOrder)->toArray());
    }

    public function testItMapsOrderDataToPennyBlackOrderDetailsWithNoGiftMessage()
    {
        $mockGiftMessage = $this->createMock(MessageInterface::class);
        $mockGiftMessage->method('getMessage')->willReturn('A lovely gift message');
        $this->mockGiftMessageRepository->method('get')->with(1)
            ->willThrowException(new NoSuchEntityException(new Phrase("oops")));

        $mapper = new OrderDetailsMapper(
            $this->mockSkusProvider,
            $this->mockProductTitlesProvider,
            $this->mockGiftMessageRepository
        );

        $exp = OrderDetails::fromValues(
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
            '',
            ['WB004'],
            ['Shoulder bag'],
            ['10PERCENTOFF']
        );

        $this->assertEquals($exp->toArray(), $mapper->map($this->mockOrder)->toArray());
    }
}

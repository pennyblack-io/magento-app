<?php

namespace PennyBlack\App\Test\Unit\Mapper;

use DateTime;
use Magento\Directory\Model\Currency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Model\OrderRepository as GiftMessageRepository;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use PennyBlack\App\Mapper\OrderMapper;
use PennyBlack\App\Provider\ProductTitlesProvider;
use PennyBlack\App\Provider\SkusProvider;
use PennyBlack\Model\Order as PennyBlackOrder;
use PHPUnit\Framework\TestCase;

class OrderMapperTest extends TestCase
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
        $this->mockOrder->method('getCreatedAt')->willReturn('2023-02-27 10:49:25');
    }

    public function testItMapsOrderDataToPennyBlackOrderDetailsWithAGiftMessage()
    {
        $mockGiftMessage = $this->createMock(MessageInterface::class);
        $mockGiftMessage->method('getMessage')->willReturn('A lovely gift message');
        $this->mockGiftMessageRepository->method('get')->with(1)->willReturn($mockGiftMessage);

        $mapper = new OrderMapper(
            $this->mockSkusProvider,
            $this->mockProductTitlesProvider,
            $this->mockGiftMessageRepository
        );

        $exp = (new PennyBlackOrder())
            ->setId(1)
            ->setNumber('0000001')
            ->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2023-02-27 10:49:25'))
            ->setCurrency('GBP')
            ->setTotalAmount(55.99)
            ->setTotalItems(1)
            ->setBillingCountry('GB')
            ->setBillingCity('London')
            ->setBillingPostcode('SE1 3JW')
            ->setShippingCountry('GB')
            ->setShippingCity('London')
            ->setShippingPostcode('SE1 3JW')
            ->setSkus(['WB004'])
            ->setGiftMessage('A lovely gift message')
            ->setProductTitles(['Shoulder bag'])
            ->setPromoCodes(['10PERCENTOFF']);

        $this->assertEquals($exp->toArray(), $mapper->map($this->mockOrder)->toArray());
    }

    public function testItMapsOrderDataToPennyBlackOrderDetailsWithNoGiftMessage()
    {
        $mockGiftMessage = $this->createMock(MessageInterface::class);
        $mockGiftMessage->method('getMessage')->willReturn('A lovely gift message');
        $this->mockGiftMessageRepository->method('get')->with(1)
            ->willThrowException(new NoSuchEntityException(new Phrase("oops")));

        $mapper = new OrderMapper(
            $this->mockSkusProvider,
            $this->mockProductTitlesProvider,
            $this->mockGiftMessageRepository
        );

        $exp = (new PennyBlackOrder())
            ->setId(1)
            ->setNumber('0000001')
            ->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', '2023-02-27 10:49:25'))
            ->setCurrency('GBP')
            ->setTotalAmount(55.99)
            ->setTotalItems(1)
            ->setBillingCountry('GB')
            ->setBillingCity('London')
            ->setBillingPostcode('SE1 3JW')
            ->setShippingCountry('GB')
            ->setShippingCity('London')
            ->setShippingPostcode('SE1 3JW')
            ->setSkus(['WB004'])
            ->setProductTitles(['Shoulder bag'])
            ->setPromoCodes(['10PERCENTOFF']);

        $this->assertEquals($exp->toArray(), $mapper->map($this->mockOrder)->toArray());
    }
}

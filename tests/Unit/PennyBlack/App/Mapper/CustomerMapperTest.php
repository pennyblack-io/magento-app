<?php

namespace PennyBlack\App\Test\Unit\Mapper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use PennyBlack\App\Mapper\CustomerMapper;
use PennyBlack\App\Provider\CustomerGroupProvider;
use PennyBlack\App\Provider\NewsletterSubscribedProvider;
use PennyBlack\App\Repository\CustomerOrderCountRepository;
use PennyBlack\App\Repository\CustomerTotalSpendRepository;
use PennyBlack\Model\Customer as PennyBlackCustomer;
use PHPUnit\Framework\TestCase;

class CustomerMapperTest extends TestCase
{
    private $mockConfig;
    private $mockOrderCountRepository;
    private $mockTotalSpendRepository;
    private $mockCustomerGroupProvider;
    private $mockNewsletterSubscribedProvider;

    private $mockOrder;
    private $mockShippingAddress;

    public function setUp(): void
    {
        $this->mockOrderCountRepository = $this->createMock(CustomerOrderCountRepository::class);
        $this->mockTotalSpendRepository = $this->createMock(CustomerTotalSpendRepository::class);
        $this->mockCustomerGroupProvider = $this->createMock(CustomerGroupProvider::class);
        $this->mockNewsletterSubscribedProvider = $this->createMock(NewsletterSubscribedProvider::class);

        $this->mockOrderCountRepository->method('getByEmail')->willReturn(21);
        $this->mockTotalSpendRepository->method('getByEmail')->willReturn(462.76);

        $mockStore = $this->createMock(Store::class);
        $mockStore->method('getId')->willReturn(1);

        $this->mockShippingAddress = $this->createMock(OrderAddressInterface::class);

        $this->mockOrder = $this->createMock(Order::class);
        $this->mockOrder->method('getStore')->willReturn($mockStore);
        $this->mockOrder->method('getShippingAddress')->willReturn($this->mockShippingAddress);
    }

    public function testItMapsACustomerFromAMagentoOrder()
    {
        $this->mockConfig = $this->createMock(ScopeConfigInterface::class);
        $this->mockConfig->method('getValue')
            ->with('general/locale/code', ScopeInterface::SCOPE_STORE, 1)
            ->willReturn('en_EN');

        $this->mockNewsletterSubscribedProvider->method('isSubscribed')->willReturn("1");

        $this->mockOrder->method('getCustomerId')->willReturn(1);
        $this->mockShippingAddress->method('getEmail')->willReturn('tim@apple.com');
        $this->mockShippingAddress->method('getFirstname')->willReturn('Tim');
        $this->mockShippingAddress->method('getLastname')->willReturn('Apple');

        $mapper = new CustomerMapper(
            $this->mockConfig,
            $this->mockOrderCountRepository,
            $this->mockTotalSpendRepository,
            $this->mockCustomerGroupProvider,
            $this->mockNewsletterSubscribedProvider
        );

        $exp = PennyBlackCustomer::fromValues(
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

        $this->assertEquals($exp->toArray(), $mapper->map($this->mockOrder)->toArray());
    }

    public function testItMapsAGuestCustomerFromAMagentoOrder()
    {
        $this->mockConfig = $this->createMock(ScopeConfigInterface::class);
        $this->mockConfig->method('getValue')
            ->with('general/locale/code', ScopeInterface::SCOPE_STORE, 1)
            ->willReturn('en_EN');

        $this->mockOrder->method('getCustomerId')->willReturn(null);
        $this->mockOrder->method('getCustomerIsGuest')->willReturn("1");
        $this->mockShippingAddress->method('getEmail')->willReturn('tim@apple.com');
        $this->mockShippingAddress->method('getFirstname')->willReturn('Tim');
        $this->mockShippingAddress->method('getLastname')->willReturn('Apple');

        $mapper = new CustomerMapper(
            $this->mockConfig,
            $this->mockOrderCountRepository,
            $this->mockTotalSpendRepository,
            $this->mockCustomerGroupProvider,
            $this->mockNewsletterSubscribedProvider
        );

        $exp = PennyBlackCustomer::fromValues(
            null,
            'Tim',
            'Apple',
            'tim@apple.com',
            'en_EN',
            '0',
            21,
            [],
            462.76
        );

        $this->assertEquals($exp->toArray(), $mapper->map($this->mockOrder)->toArray());
    }

    public function testItMapsACustomerFromAMagentoOrderWhenLocaleConfigIsNotFound()
    {
        $this->mockConfig = $this->createMock(ScopeConfigInterface::class);
        $this->mockConfig->method('getValue')
            ->with('general/locale/code', ScopeInterface::SCOPE_STORE, 1)
            ->willReturn(null);

        $this->mockNewsletterSubscribedProvider->method('isSubscribed')->willReturn("1");

        $this->mockOrder->method('getCustomerId')->willReturn(1);
        $this->mockShippingAddress->method('getEmail')->willReturn('tim@apple.com');
        $this->mockShippingAddress->method('getFirstname')->willReturn('Tim');
        $this->mockShippingAddress->method('getLastname')->willReturn('Apple');

        $mapper = new CustomerMapper(
            $this->mockConfig,
            $this->mockOrderCountRepository,
            $this->mockTotalSpendRepository,
            $this->mockCustomerGroupProvider,
            $this->mockNewsletterSubscribedProvider
        );

        $exp = PennyBlackCustomer::fromValues(
            1,
            'Tim',
            'Apple',
            'tim@apple.com',
            '',
            '1',
            21,
            [],
            462.76
        );

        $this->assertEquals($exp->toArray(), $mapper->map($this->mockOrder)->toArray());
    }
}

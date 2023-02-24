<?php

namespace PennyBlack\App\Test\Unit\Mapper;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use PennyBlack\App\Mapper\CustomerMapper;
use PennyBlack\App\Repository\CustomerOrderCountRepository;
use PennyBlack\App\Repository\CustomerTotalSpendRepository;
use PennyBlack\Model\Customer as PennyBlackCustomer;
use PHPUnit\Framework\TestCase;

class CustomerMapperTest extends TestCase
{
    private $mockConfig;
    private $mockOrderCountRepository;
    private $mockTotalSpendRepository;

    private $mockOrder;

    public function setUp(): void
    {
        $this->mockConfig = $this->createMock(ScopeConfigInterface::class);
        $this->mockConfig->method('getValue')
            ->with('general/locale/code', ScopeInterface::SCOPE_STORE, 1)
            ->willReturn('en_EN');

        $this->mockOrderCountRepository = $this->createMock(CustomerOrderCountRepository::class);
        $this->mockTotalSpendRepository = $this->createMock(CustomerTotalSpendRepository::class);

        $this->mockOrderCountRepository->method('getByEmail')->willReturn(21);
        $this->mockTotalSpendRepository->method('getByEmail')->willReturn(462.76);

        $mockBillingAddress = $this->createMock(OrderAddressInterface::class);

        $this->mockOrder = $this->createMock(Order::class);
        $this->mockOrder->method('getBillingAddress')->willReturn($mockBillingAddress);
    }

    public function testItMapsACustomerFromAMagentoOrder()
    {
        $mockCustomer = $this->createMock(Customer::class);
        $mockCustomer->method('getEmail')->willReturn('tim@apple.com');
        $mockCustomer->method('getFirstName')->willReturn('Tim');
        $mockCustomer->method('getLastName')->willReturn('Apple');

        $this->mockOrder->method('getCustomer')->willReturn($mockCustomer);

        $mapper = new CustomerMapper(
            $this->mockConfig, $this->mockOrderCountRepository, $this->mockTotalSpendRepository
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

        $this->assertEquals($exp->toArray(), $mapper->map($this->mockOrder));
    }
}

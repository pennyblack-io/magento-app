<?php

namespace PennyBlack\App\Test\Unit\Provider;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Sales\Model\Order;
use PennyBlack\App\Provider\CustomerGroupProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;

class CustomerGroupProviderTest extends TestCase
{
    private $mockGroupRepository;
    private $mockLogger;

    public function setUp(): void
    {
        $this->mockGroupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
    }

    public function testItReturnsNullIfCustomerGroupIsNull()
    {
        $mockOrder = $this->createMock(Order::class);
        $mockOrder->method('getCustomerGroupId')->willReturn(null);

        $groupProvider = new CustomerGroupProvider($this->mockGroupRepository, $this->mockLogger);

        $this->assertNull($groupProvider->getFromOrder($mockOrder));
    }

    public function testItReturnsNullIfNoGroupIsFoundWithId()
    {
        $mockOrder = $this->createMock(Order::class);
        $mockOrder->method('getCustomerGroupId')->willReturn(1);

        $this->mockGroupRepository->method('getById')->with(1)
            ->willThrowException(new NoSuchEntityException(new Phrase('oops')));

        $groupProvider = new CustomerGroupProvider($this->mockGroupRepository, $this->mockLogger);

        $this->mockLogger->expects($this->once())->method('error')->with('oops');

        $this->assertNull($groupProvider->getFromOrder($mockOrder));
    }

    public function testItReturnsNullIfLocalizedExceptionOccurs()
    {
        $mockOrder = $this->createMock(Order::class);
        $mockOrder->method('getCustomerGroupId')->willReturn(1);

        $this->mockGroupRepository->method('getById')->with(1)
            ->willThrowException(new LocalizedException(new Phrase('oops')));

        $groupProvider = new CustomerGroupProvider($this->mockGroupRepository, $this->mockLogger);

        $this->mockLogger->expects($this->once())->method('error')->with('oops');

        $this->assertNull($groupProvider->getFromOrder($mockOrder));
    }

    public function testItProvidesACustomerGroup()
    {
        $mockOrder = $this->createMock(Order::class);
        $mockOrder->method('getCustomerGroupId')->willReturn(1);

        $mockGroup = $this->createMock(GroupInterface::class);

        $this->mockGroupRepository->method('getById')->with(1)
            ->willReturn($mockGroup);

        $groupProvider = new CustomerGroupProvider($this->mockGroupRepository, $this->mockLogger);

        $this->mockLogger->expects($this->exactly(0))->method('error');

        $res = $groupProvider->getFromOrder($mockOrder);

        $this->assertNotNull($res);
        $this->assertEquals($mockGroup, $res);
    }
}

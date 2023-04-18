<?php

namespace PennyBlack\App\Test\Unit\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PennyBlack\Api as PennyBlackApi;
use PennyBlack\App\ApiConnector\Client;
use PennyBlack\App\Exception\MissingApiConfigException;
use PennyBlack\App\Observer\ConfigSaveObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConfigSaveObserverTest extends TestCase
{
    /** @var MockObject|Client */
    private $mockClient;

    /** @var MockObject|StoreManagerInterface */
    private $mockStoreManager;

    /** @var MockObject|LoggerInterface */
    private $mockLogger;

    public function setUp(): void
    {
        $this->mockClient = $this->createMock(Client::class);
        $this->mockStoreManager = $this->createMock(StoreManager::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
    }

    public function testItDoesNothingIfApiKeyConfigHasNotChanged(): void
    {
        $mockPennyBlackApi = $this->createMock(PennyBlackApi::class);
        $this->mockClient->method('getApiClient')->willReturn($mockPennyBlackApi);
        $configSaveObserver = new ConfigSaveObserver($this->mockClient, $this->mockStoreManager, $this->mockLogger);

        /** @var MockObject|Observer $observer */
        $observer = $this->createStub(Observer::class);
        $observer->method('getData')->willReturn(['changed_paths' => 'a/different/config/path']);

        $this->mockStoreManager->expects($this->exactly(0))->method('getStore');
        $mockPennyBlackApi->expects($this->exactly(0))->method('installStore');
        $this->mockLogger->expects($this->exactly(0))->method('error');

        $configSaveObserver->execute($observer);
    }

    public function testItDoesNothingIfKeyCannotBeFoundInObservedEvent(): void
    {
        $mockPennyBlackApi = $this->createMock(PennyBlackApi::class);
        $this->mockClient->method('getApiClient')->willReturn($mockPennyBlackApi);
        $configSaveObserver = new ConfigSaveObserver($this->mockClient, $this->mockStoreManager, $this->mockLogger);

        /** @var MockObject|Observer $observer */
        $observer = $this->createStub(Observer::class);
        $observer->method('getData')->willReturn([]);

        $this->mockStoreManager->expects($this->exactly(0))->method('getStore');
        $mockPennyBlackApi->expects($this->exactly(0))->method('installStore');
        $this->mockLogger->expects($this->exactly(0))->method('error');

        $configSaveObserver->execute($observer);
    }

    public function testItRegistersTheStoreWithPennyBlackWhenApiKeyIsSubmitted(): void
    {
        $mockPennyBlackApi = $this->createMock(PennyBlackApi::class);
        $this->mockClient->method('getApiClient')->willReturn($mockPennyBlackApi);

        $mockPennyBlackApi->expects($this->once())
            ->method('installStore')
            ->with('https://my-store.com');

        $mockStore = $this->createMock(Store::class);
        $mockStore->method('getBaseUrl')->willReturn('https://my-store.com');
        $this->mockStoreManager->method('getStore')->willReturn($mockStore);

        /** @var MockObject|Observer $observer */
        $observer = $this->createStub(Observer::class);
        $observer->method('getData')->willReturn(['pennyblack/general/api_key']);

        $this->mockLogger->expects($this->exactly(0))->method('error');

        $configSaveObserver = new ConfigSaveObserver($this->mockClient, $this->mockStoreManager, $this->mockLogger);
        $configSaveObserver->execute($observer);
    }

    public function testItLogsAnErrorIfStoreCannotBeFound(): void
    {
        $mockPennyBlackApi = $this->createMock(PennyBlackApi::class);
        $this->mockClient->method('getApiClient')->willReturn($mockPennyBlackApi);

        $mockPennyBlackApi->expects($this->exactly(0))
            ->method('installStore')
            ->with('https://my-store.com');

        $mockStore = $this->createMock(Store::class);
        $mockStore->expects($this->exactly(0))->method('getBaseUrl');
        $this->mockStoreManager->method('getStore')->willThrowException(new Exception("oops"));

        /** @var MockObject|Observer $observer */
        $observer = $this->createStub(Observer::class);
        $observer->method('getData')->willReturn(['pennyblack/general/api_key']);

        $this->mockLogger->expects($this->exactly(1))->method('error')->with("oops");

        $configSaveObserver = new ConfigSaveObserver($this->mockClient, $this->mockStoreManager, $this->mockLogger);
        $configSaveObserver->execute($observer);
    }

    public function testItLogsAnErrorIfApiConfigIsNotFound(): void
    {
        $this->mockClient->method('getApiClient')
            ->willThrowException(new MissingApiConfigException("oops"));

        $mockStore = $this->createMock(Store::class);
        $mockStore->method('getBaseUrl')->willReturn('https://my-store.com');
        $this->mockStoreManager->method('getStore')->willReturn($mockStore);

        /** @var MockObject|Observer $observer */
        $observer = $this->createStub(Observer::class);
        $observer->method('getData')->willReturn(['pennyblack/general/api_key']);

        $this->mockLogger->expects($this->exactly(1))->method('error')->with("oops");

        $configSaveObserver = new ConfigSaveObserver($this->mockClient, $this->mockStoreManager, $this->mockLogger);
        $configSaveObserver->execute($observer);
    }

    public function testItLogsAnErrorIfInstallRequestFails(): void
    {
        $mockPennyBlackApi = $this->createMock(PennyBlackApi::class);
        $mockPennyBlackApi->method('installStore')->willThrowException(new Exception("oops"));

        $this->mockClient->method('getApiClient')->willReturn($mockPennyBlackApi);

        $mockStore = $this->createMock(Store::class);
        $mockStore->method('getBaseUrl')->willReturn('https://my-store.com');
        $this->mockStoreManager->method('getStore')->willReturn($mockStore);

        /** @var MockObject|Observer $observer */
        $observer = $this->createStub(Observer::class);
        $observer->method('getData')->willReturn(['pennyblack/general/api_key']);

        $this->mockLogger->expects($this->exactly(1))->method('error')->with("oops");

        $configSaveObserver = new ConfigSaveObserver($this->mockClient, $this->mockStoreManager, $this->mockLogger);
        $configSaveObserver->execute($observer);
    }
}

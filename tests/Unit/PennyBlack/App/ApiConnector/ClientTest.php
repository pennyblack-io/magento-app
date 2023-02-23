<?php

namespace PennyBlack\PennyBlack\tests\Unit\PennyBlack\App\ApiConnector;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PennyBlack\Api;
use PennyBlack\App\ApiConnector\Client;
use PennyBlack\App\Exception\MissingApiConfigException;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testItCreatesAPennyBlackApiClient(): void
    {
        $mockConfig = $this->createMock(ScopeConfigInterface::class);
        $mockConfig->expects($this->exactly(2))->method('getValue')->willReturnOnConsecutiveCalls(
            'c8079b7f-5599-45ea-8a85-88ec461faa84',
            '1'
        );

        $client = new Client($mockConfig);

        $this->assertInstanceOf(Api::class, $client->getApiClient());
    }

    public function testItThrowsAnExceptionIfNoConfigValuesAreFound(): void
    {
        $mockConfig = $this->createMock(ScopeConfigInterface::class);
        $mockConfig->expects($this->exactly(2))->method('getValue')->willReturnOnConsecutiveCalls(
            null,
            null
        );

        $this->expectException(MissingApiConfigException::class);

        $client = new Client($mockConfig);
        $client->getApiClient();
    }

    public function testItThrowsAnExceptionIfApiKeyIsNotFound(): void
    {
        $mockConfig = $this->createMock(ScopeConfigInterface::class);
        $mockConfig->expects($this->exactly(2))->method('getValue')->willReturnOnConsecutiveCalls(
            null,
            '1'
        );

        $this->expectException(MissingApiConfigException::class);

        $client = new Client($mockConfig);
        $client->getApiClient();
    }

    public function testItThrowsAnExceptionIfTestModeConfigIsNotFound(): void
    {
        $mockConfig = $this->createMock(ScopeConfigInterface::class);
        $mockConfig->expects($this->exactly(2))->method('getValue')->willReturnOnConsecutiveCalls(
            'c8079b7f-5599-45ea-8a85-88ec461faa84',
            null
        );

        $this->expectException(MissingApiConfigException::class);

        $client = new Client($mockConfig);
        $client->getApiClient();
    }
}

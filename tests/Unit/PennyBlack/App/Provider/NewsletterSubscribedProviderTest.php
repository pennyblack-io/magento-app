<?php

namespace PennyBlack\App\Test\Unit\Provider;

use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PennyBlack\App\Provider\NewsletterSubscribedProvider;
use PHPUnit\Framework\TestCase;

class NewsletterSubscribedProviderTest extends TestCase
{
    private $mockSubscriber;
    private $mockStoreManager;

    public function setUp(): void
    {
        $this->mockSubscriber = $this->createMock(Subscriber::class);
        $this->mockStoreManager = $this->createMock(StoreManagerInterface::class);
    }

    public function testItProvidesASubscribedValueForACustomerId()
    {
        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getId')->willReturn(1);
        $this->mockStoreManager->method('getWebsite')->willReturn($website);

        $this->mockSubscriber->method('loadByCustomerId')->with(5, 1)->willReturn([
            'subscriber_status' => '1'
        ]);

        $provider = new NewsletterSubscribedProvider($this->mockSubscriber, $this->mockStoreManager);

        $isSubscribed = $provider->isSubscribed(5);

        $this->assertEquals('1', $isSubscribed);
    }

    public function testItIsFalseWhenNoSubscriberRecordIsFound()
    {
        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getId')->willReturn(1);
        $this->mockStoreManager->method('getWebsite')->willReturn($website);

        $this->mockSubscriber->method('loadByCustomerId')->with(5, 1)->willReturn([]);

        $provider = new NewsletterSubscribedProvider($this->mockSubscriber, $this->mockStoreManager);

        $isSubscribed = $provider->isSubscribed(5);

        $this->assertEquals('0', $isSubscribed);
    }
}

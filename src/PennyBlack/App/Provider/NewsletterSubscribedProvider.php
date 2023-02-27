<?php

namespace PennyBlack\App\Provider;

use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Store\Model\StoreManagerInterface;

class NewsletterSubscribedProvider
{
    private Subscriber $subscriber;
    private StoreManagerInterface $storeManager;

    public function __construct(Subscriber $subscriber, StoreManagerInterface $storeManager)
    {
        $this->subscriber = $subscriber;
        $this->storeManager = $storeManager;
    }

    public function isSubscribed(int $customerId): string
    {
        $websiteId = $this->storeManager->getWebsite()->getId();

        $subscriber = $this->subscriber->loadByCustomerId($customerId, $websiteId);

        if (!array_key_exists('subscriber_status', $subscriber)) {
            return '0';
        }

        return $subscriber['subscriber_status'];
    }
}

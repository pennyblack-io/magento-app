<?php

namespace PennyBlack\App\Repository;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;

class NewsletterSubscribedRepository
{
    private ResourceConnection $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function isSubscribedInStore(int $customerId, Store $store): bool
    {
        $connection = $this->resourceConnection->getConnection();

        $sql = sprintf(
            'SELECT subscriber_status FROM %s n WHERE n.customer_id = :customer_id AND n.store_id = :store_id;',
            'newsletter_subscriber',
        );

        $query = $connection->query($sql, [
            'customer_id' => $customerId,
            'store_id' => $store->getId(),
        ]);

        $result = $query->fetch();
        if (!$result) {
            return false;
        }

        return $result['subscriber_status'];
    }
}

<?php

namespace PennyBlack\App\Repository;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;

class CustomerOrderCountRepository
{
    private CollectionFactoryInterface $collectionFactory;

    public function __construct(CollectionFactoryInterface $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function getByEmail(string $email): int
    {
        return $this->collectionFactory->create()
            ->addAttributeToFilter('customer_email', $email)->count();
    }
}

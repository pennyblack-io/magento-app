<?php

namespace PennyBlack\App\Repository;

use Magento\Framework\App\ResourceConnection;

class CustomerTotalSpendRepository
{
    private ResourceConnection $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function getByEmail(string $email): float
    {
        $connection = $this->resourceConnection->getConnection();

        $sql = sprintf(
            'SELECT SUM(o.grand_total) AS customer_total FROM %s o WHERE o.customer_email = :email;',
            'sales_order',
        );

        $query = $connection->query($sql, ['email' => $email]);

        return (float) $query->fetch()['customer_total'];
    }
}

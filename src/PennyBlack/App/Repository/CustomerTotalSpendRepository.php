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

        $sql = 'SELECT SUM(o.grand_total) AS customer_total FROM sales_order o WHERE o.customer_email = :email;';

        $query = $connection->query($sql, ['email' => $email]);

        return (float) $query->fetch()['customer_total'];
    }
}

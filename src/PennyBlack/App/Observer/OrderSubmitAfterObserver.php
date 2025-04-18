<?php

namespace PennyBlack\App\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use PennyBlack\App\ApiConnector\Client;
use PennyBlack\App\Mapper\CustomerMapper;
use PennyBlack\App\Mapper\OrderMapper;
use Psr\Log\LoggerInterface;

class OrderSubmitAfterObserver implements ObserverInterface
{
    private const ORIGIN_MAGENTO = 'magento';

    private Client $client;
    private OrderMapper $orderMapper;
    private CustomerMapper $customerMapper;
    private LoggerInterface $logger;

    public function __construct(
        Client $client,
        OrderMapper $orderMapper,
        CustomerMapper $customerMapper,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->orderMapper = $orderMapper;
        $this->customerMapper = $customerMapper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var Order|null $order */
        $order = $observer->getData('order');
        if ($order === null) {
            $this->logger->error('Unable to send order information to Penny Black, order not found in event.');

            return;
        }

        try {
            $client = $this->client->getApiClient();

            $client->sendOrder(
                $this->orderMapper->map($order),
                $this->customerMapper->map($order),
                self::ORIGIN_MAGENTO
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}

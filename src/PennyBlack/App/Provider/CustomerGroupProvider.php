<?php

namespace PennyBlack\App\Provider;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class CustomerGroupProvider
{
    private GroupRepositoryInterface $groupRepository;
    private LoggerInterface $logger;

    public function __construct(GroupRepositoryInterface $groupRepository, LoggerInterface $logger)
    {
        $this->groupRepository = $groupRepository;
        $this->logger = $logger;
    }

    public function getFromOrder(Order $order): ?GroupInterface
    {
        $groupId = $order->getCustomerGroupId();
        if ($groupId === null) {
            return $groupId;
        }

        try {
            return $this->groupRepository->getById($order->getCustomerGroupId());
        } catch (NoSuchEntityException | LocalizedException $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }
}

<?php

namespace PennyBlack\App\Mapper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use PennyBlack\App\Repository\CustomerOrderCountRepository;
use PennyBlack\App\Repository\CustomerTotalSpendRepository;
use PennyBlack\Model\Customer as PennyBlackCustomer;

class CustomerMapper
{
    private const LOCALE_CONFIG_PATH = 'general/locale/code';

    private ScopeConfigInterface $scopeConfig;
    private CustomerOrderCountRepository $orderCountRepository;
    private CustomerTotalSpendRepository $totalSpendRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerOrderCountRepository $orderCountRepository,
        CustomerTotalSpendRepository $totalSpendRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderCountRepository = $orderCountRepository;
        $this->totalSpendRepository = $totalSpendRepository;
    }

    public function map(Order $order): PennyBlackCustomer
    {
        $customer = $order->getCustomer();
        $billingAddress = $order->getBillingAddress();
        $email = $customer ? $customer->getEmail() : $billingAddress->getEmail();

        $store = $order->getStore();
        $locale = $this->scopeConfig->getValue(self::LOCALE_CONFIG_PATH, ScopeInterface::SCOPE_STORE, $store->getId());

        return PennyBlackCustomer::fromValues(
            $customer ? $customer->getId() : 1,
            $customer ? $customer->getFirstName() : $billingAddress->getFirstname(),
            $customer ? $customer->getLastname() : $billingAddress->getLastName(),
            $email,
            $locale ?? '',
            '1',
            $this->orderCountRepository->getByEmail($email),
            [],
            $this->totalSpendRepository->getByEmail($email)
        );
    }
}

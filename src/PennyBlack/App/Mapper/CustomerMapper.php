<?php

namespace PennyBlack\App\Mapper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use PennyBlack\App\Provider\CustomerGroupProvider;
use PennyBlack\App\Provider\NewsletterSubscribedProvider;
use PennyBlack\App\Repository\CustomerOrderCountRepository;
use PennyBlack\App\Repository\CustomerTotalSpendRepository;
use PennyBlack\Model\Customer as PennyBlackCustomer;

class CustomerMapper
{
    private const LOCALE_CONFIG_PATH = 'general/locale/code';

    private ScopeConfigInterface $scopeConfig;
    private CustomerOrderCountRepository $orderCountRepository;
    private CustomerTotalSpendRepository $totalSpendRepository;
    private CustomerGroupProvider $customerGroupProvider;
    private NewsletterSubscribedProvider $newsletterSubscribedProvider;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerOrderCountRepository $orderCountRepository,
        CustomerTotalSpendRepository $totalSpendRepository,
        CustomerGroupProvider $customerGroupProvider,
        NewsletterSubscribedProvider $newsletterSubscribedProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderCountRepository = $orderCountRepository;
        $this->totalSpendRepository = $totalSpendRepository;
        $this->customerGroupProvider = $customerGroupProvider;
        $this->newsletterSubscribedProvider = $newsletterSubscribedProvider;
    }

    public function map(Order $order): PennyBlackCustomer
    {
        $shippingAddress = $order->getShippingAddress();
        $email = $shippingAddress->getEmail();
        $customerGroup = $this->customerGroupProvider->getFromOrder($order);

        return PennyBlackCustomer::fromValues(
            $order->getCustomerId(),
            $shippingAddress->getFirstname(),
            $shippingAddress->getLastName(),
            $email,
            $this->getLocale($order) ?? '',
            $this->isMarketingSubscribed($order),
            $this->orderCountRepository->getByEmail($email),
            $customerGroup ? [$customerGroup->getCode()] : [],
            $this->totalSpendRepository->getByEmail($email)
        );
    }

    private function isMarketingSubscribed(Order $order): string
    {
        if ((bool) $order->getCustomerIsGuest()) {
            return '0';
        }

        return $this->newsletterSubscribedProvider->isSubscribed(
            $order->getCustomerId()
        );
    }

    private function getLocale(Order $order): ?string
    {
        $store = $order->getStore();

        return $this->scopeConfig->getValue(
            self::LOCALE_CONFIG_PATH, ScopeInterface::SCOPE_STORE, $store->getId()
        );
    }
}

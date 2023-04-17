<?php

namespace PennyBlack\App\Mapper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use PennyBlack\App\Provider\CustomerGroupProvider;
use PennyBlack\App\Repository\CustomerOrderCountRepository;
use PennyBlack\App\Repository\CustomerTotalSpendRepository;
use PennyBlack\App\Repository\NewsletterSubscribedRepository;
use PennyBlack\Model\Customer;
use PennyBlack\Model\Customer as PennyBlackCustomer;

class CustomerMapper
{
    private const LOCALE_CONFIG_PATH = 'general/locale/code';

    private ScopeConfigInterface $scopeConfig;
    private CustomerOrderCountRepository $orderCountRepository;
    private CustomerTotalSpendRepository $totalSpendRepository;
    private CustomerGroupProvider $customerGroupProvider;
    private NewsletterSubscribedRepository $newsletterSubscribedRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CustomerOrderCountRepository $orderCountRepository,
        CustomerTotalSpendRepository $totalSpendRepository,
        CustomerGroupProvider $customerGroupProvider,
        NewsletterSubscribedRepository $newsletterSubscribedRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderCountRepository = $orderCountRepository;
        $this->totalSpendRepository = $totalSpendRepository;
        $this->customerGroupProvider = $customerGroupProvider;
        $this->newsletterSubscribedRepository = $newsletterSubscribedRepository;
    }

    public function map(Order $order): PennyBlackCustomer
    {
        $shippingAddress = $order->getShippingAddress();
        $email = $shippingAddress->getEmail();
        $customerGroup = $this->customerGroupProvider->getFromOrder($order);
        $store = $order->getStore();

        $customer = (new Customer())
            ->setFirstName($shippingAddress->getFirstname())
            ->setLastName($shippingAddress->getLastname())
            ->setEmail($email)
            ->setLanguage($this->getLocale($store))
            ->setMarketingConsent($this->isMarketingSubscribed($order, $store))
            ->setTotalOrders($this->orderCountRepository->getByEmail($email))
            ->setTags($customerGroup ? [$customerGroup->getCode()] : [])
            ->setTotalSpent($this->totalSpendRepository->getByEmail($email));

        // Only set customer ID for non-guest orders.
        if ($customerId = $order->getCustomerId()) {
            $customer->setVendorCustomerId((string)$customerId);
        }

        return $customer;
    }

    private function isMarketingSubscribed(Order $order, Store $store): bool
    {
        if ((bool) $order->getCustomerIsGuest()) {
            return false;
        }

        return $this->newsletterSubscribedRepository->isSubscribedInStore(
            $order->getCustomerId(),
            $store
        );
    }

    private function getLocale(Store $store): string
    {
        $localeCode = $this->scopeConfig->getValue(
            self::LOCALE_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE,
            $store->getId()
        ) ?? '';

        return strstr($localeCode, '_', true);
    }
}

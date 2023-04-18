<?php

namespace PennyBlack\App\Mapper;

use DateTime;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftMessage\Model\OrderRepository as GiftMessageRepository;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order;
use PennyBlack\App\Provider\ProductTitlesProvider;
use PennyBlack\App\Provider\SkusProvider;
use PennyBlack\Model\Order as PennyBlackOrder;

class OrderMapper
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    private SkusProvider $skusProvider;
    private ProductTitlesProvider $productTitlesProvider;
    private GiftMessageRepository $giftMessageRepository;

    public function __construct(
        SkusProvider $skusProvider,
        ProductTitlesProvider $productTitlesProvider,
        GiftMessageRepository $giftMessageRepository
    ) {
        $this->skusProvider = $skusProvider;
        $this->productTitlesProvider = $productTitlesProvider;
        $this->giftMessageRepository = $giftMessageRepository;
    }

    public function map(Order $order): PennyBlackOrder
    {
        $pennyBlackOrder = (new PennyBlackOrder())
            ->setId($order->getId())
            ->setNumber($order->getIncrementId())
            ->setCreatedAt(DateTime::createFromFormat(self::DATE_FORMAT, $order->getCreatedAt()))
            ->setCurrency($order->getOrderCurrency()->getCurrencyCode())
            ->setTotalAmount($order->getBaseGrandTotal())
            ->setTotalItems(count($order->getItems()))
            ->setSkus($this->skusProvider->get($order))
            ->setGiftMessage($this->getGiftMessage($order))
            ->setProductTitles($this->productTitlesProvider->get($order))
            ->setPromoCodes($order->getCouponCode() ? [$order->getCouponCode()] : []);

        if ($billingAddress = $order->getBillingAddress()) {
            $this->setAddressData($pennyBlackOrder, $billingAddress, false);
        }

        if ($shippingAddress = $order->getShippingAddress()) {
            $this->setAddressData($pennyBlackOrder, $shippingAddress, true);
        }

        return $pennyBlackOrder;
    }

    private function setAddressData(PennyBlackOrder $order, OrderAddressInterface $address, bool $isShipping): void
    {
        if ($isShipping) {
            $order
                ->setShippingCountry($address->getCountryId())
                ->setShippingCity($address->getCity())
                ->setShippingPostcode($address->getPostcode());

            return;
        }

        $order
            ->setBillingCountry($address->getCountryId())
            ->setBillingCity($address->getCity())
            ->setBillingPostcode($address->getPostcode());
    }

    private function getGiftMessage(Order $order): string
    {
        try {
            $message = $this->giftMessageRepository->get($order->getId());

            return $message->getMessage();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }
}

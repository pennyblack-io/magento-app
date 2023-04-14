<?php

namespace PennyBlack\App\Mapper;

use DateTime;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftMessage\Model\OrderRepository as GiftMessageRepository;
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
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        return (new PennyBlackOrder())
            ->setId($order->getId())
            ->setNumber($order->getIncrementId())
            ->setCreatedAt(DateTime::createFromFormat(self::DATE_FORMAT, $order->getCreatedAt()))
            ->setCurrency($order->getOrderCurrency()->getCurrencyCode())
            ->setTotalAmount($order->getBaseGrandTotal())
            ->setTotalItems(count($order->getItems()))
            ->setBillingCountry($billingAddress->getCountryId())
            ->setBillingCity($billingAddress->getCity())
            ->setBillingPostcode($billingAddress->getPostcode())
            ->setShippingCountry($shippingAddress->getCountryId())
            ->setShippingCity($shippingAddress->getCity())
            ->setShippingPostcode($shippingAddress->getPostcode())
            ->setSkus($this->skusProvider->get($order))
            ->setGiftMessage($this->getGiftMessage($order))
            ->setProductTitles($this->productTitlesProvider->get($order))
            ->setPromoCodes($order->getCouponCode() ? [$order->getCouponCode()] : []);
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

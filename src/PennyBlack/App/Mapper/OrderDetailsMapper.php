<?php

namespace PennyBlack\App\Mapper;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftMessage\Model\OrderRepository as GiftMessageRepository;
use Magento\Sales\Model\Order;
use PennyBlack\App\Provider\ProductTitlesProvider;
use PennyBlack\App\Provider\SkusProvider;
use PennyBlack\Model\OrderDetails as PennyBlackOrderDetails;

class OrderDetailsMapper
{
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

    public function map(Order $order): PennyBlackOrderDetails
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        return PennyBlackOrderDetails::fromValues(
            $order->getIncrementId(),
            $order->getBaseGrandTotal(),
            count($order->getItems()),
            $billingAddress->getCountryId(),
            $billingAddress->getPostcode(),
            $billingAddress->getCity(),
            $shippingAddress->getCountryId(),
            $shippingAddress->getPostcode(),
            $shippingAddress->getCity(),
            $order->getOrderCurrency()->getCurrencyCode(),
            $this->getGiftMessage($order),
            $this->skusProvider->get($order),
            $this->productTitlesProvider->get($order),
            $order->getCouponCode() ? [$order->getCouponCode()] : [],
        );
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

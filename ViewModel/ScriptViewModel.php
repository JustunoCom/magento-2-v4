<?php
namespace Justuno\M2\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;

class ScriptViewModel implements ArgumentInterface
{
    private $customerSession;
    private $checkoutSession;
    private $registry;
    private $orderFactory;

    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Registry $registry,
        OrderFactory $orderFactory
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }

    public function isProductPage()
    {
        return $this->registry->registry('current_product') !== null;
    }

    public function getProductId()
    {
        $product = $this->registry->registry('current_product');
        return $product ? $product->getId() : null;
    }

    public function getOrderData()
    {
        $lastOrderId = $this->checkoutSession->getLastOrderId();
        if (!$lastOrderId) {
            return null;
        }

        $order = $this->orderFactory->create()->load($lastOrderId);
        if (!$order->getId()) {
            return null;
        }

        $orderItems = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $orderItems[] = [
                'productID' => (string)$item->getProductId(),
                'variationID' => (string)$item->getItemId(),
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'qty' => (int)$item->getQtyOrdered(),
                'price' => (int)($item->getPrice() * 100)
            ];
        }

        return [
            'orderID' => (string)$order->getIncrementId(),
            'grandTotal' => (int)($order->getGrandTotal() * 100),
            'subTotal' => (int)($order->getSubtotal() * 100),
            'tax' => (int)($order->getTaxAmount() * 100),
            'shipping' => (int)($order->getShippingAmount() * 100),
            'discount' => (int)(abs($order->getDiscountAmount()) * 100),
            'currency' => $order->getOrderCurrencyCode(),
            'discountCodes' => $order->getCouponCode() ? [$order->getCouponCode()] : [],
            'cartItems' => $orderItems
        ];
    }
}
<?php
namespace Justuno\M2\Plugin;

use Magento\Checkout\Controller\Cart\CouponPost;
use Magento\Framework\Controller\Result\RedirectFactory;

class CheckoutCartCouponPost
{
    protected $redirectFactory;

    public function __construct(RedirectFactory $redirectFactory)
    {
        $this->redirectFactory = $redirectFactory;
    }

    public function aroundExecute(CouponPost $subject, \Closure $proceed)
    {
        $couponCode = $subject->getRequest()->getParam('coupon_code');
        
        // Your custom logic here, e.g., logging or validation

        $result = $proceed();

        // After coupon application logic
        // You can add custom actions here

        return $result;
    }
}
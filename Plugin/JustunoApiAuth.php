<?php
namespace Justuno\M2\Plugin;

use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Authorization\Model\UserContextInterface;

class JustunoApiAuth
{
    protected $request;
    protected $scopeConfig;

    public function __construct(Request $request, ScopeConfigInterface $scopeConfig)
    {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }

    public function aroundGetUserType(
        \Magento\Webapi\Model\Authorization\TokenUserContext $subject,
        \Closure $proceed
    ) {
        $token = $this->request->getHeader('Authorization');
        if ($token) {
            $token = str_replace('Bearer ', '', $token);
            $configToken = $this->scopeConfig->getValue('justuno/general/woocommerce_token');
            if ($token === $configToken) {
                return UserContextInterface::USER_TYPE_INTEGRATION;
            }
        }
        return $proceed();
    }

    public function aroundGetUserId(
        \Magento\Webapi\Model\Authorization\TokenUserContext $subject,
        \Closure $proceed
    ) {
        $token = $this->request->getHeader('Authorization');
        if ($token) {
            $token = str_replace('Bearer ', '', $token);
            $configToken = $this->scopeConfig->getValue('justuno/general/woocommerce_token');
            if ($token === $configToken) {
                // Return the integration ID from your console output
                return 2;
            }
        }
        return $proceed();
    }
}
<?php
namespace Justuno\M2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_API_KEY = 'justuno/general/api_key';
    const XML_PATH_WOOCOMMERCE_TOKEN = 'justuno/general/woocommerce_token';
    const XML_PATH_SUB_DOMAIN = 'justuno/general/sub_domain';

    public function getApiKey($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getWooCommerceToken($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WOOCOMMERCE_TOKEN, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getSubDomain($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUB_DOMAIN, ScopeInterface::SCOPE_STORE, $store);
    }
}
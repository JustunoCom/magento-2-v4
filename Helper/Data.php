<?php
namespace Justuno\M2\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_API_KEY = 'justuno/general/api_key';
    const XML_PATH_WOOCOMMERCE_TOKEN = 'justuno/general/woocommerce_token';
    const XML_PATH_SUB_DOMAIN = 'justuno/general/sub_domain';
    const XML_PATH_WEBSITE_ID = 'justuno/general/website_id';

    protected $storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

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

    public function getWebsiteId($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WEBSITE_ID, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Find the website ID whose configured token matches the given token —
     * but only when the token is actually *overridden* at the website/store
     * scope, not merely inherited from default.
     *
     * This is what preserves backward compatibility for existing single-site
     * installations: their token lives at the default scope, every website
     * inherits that same value, and the helper correctly returns null instead
     * of mistakenly latching onto the first website's id and filtering feeds
     * that previously weren't filtered.
     *
     * @param string $token
     * @return int|null
     */
    public function getWebsiteIdFromToken($token)
    {
        if (!$token) {
            return null;
        }

        $defaultToken = $this->scopeConfig->getValue(self::XML_PATH_WOOCOMMERCE_TOKEN);

        foreach ($this->storeManager->getWebsites() as $website) {
            $websiteToken = $this->scopeConfig->getValue(
                self::XML_PATH_WOOCOMMERCE_TOKEN,
                ScopeInterface::SCOPE_WEBSITES,
                $website->getId()
            );
            // Skip websites that just inherit the default — they aren't a
            // distinct, scoped configuration.
            if (!$websiteToken || $websiteToken === $defaultToken) {
                continue;
            }
            if (hash_equals((string) $websiteToken, (string) $token)) {
                return (int) $website->getId();
            }
        }

        foreach ($this->storeManager->getStores() as $store) {
            $storeToken = $this->scopeConfig->getValue(
                self::XML_PATH_WOOCOMMERCE_TOKEN,
                ScopeInterface::SCOPE_STORE,
                $store->getId()
            );
            if (!$storeToken || $storeToken === $defaultToken) {
                continue;
            }
            $websiteToken = $this->scopeConfig->getValue(
                self::XML_PATH_WOOCOMMERCE_TOKEN,
                ScopeInterface::SCOPE_WEBSITES,
                $store->getWebsiteId()
            );
            if ($storeToken === $websiteToken) {
                continue; // store inherits the website override, not a fresh scope
            }
            if (hash_equals((string) $storeToken, (string) $token)) {
                return (int) $store->getWebsiteId();
            }
        }

        return null;
    }

    /**
     * Returns true if the given token matches any configured token (default,
     * website, or store scope).
     *
     * @param string $token
     * @return bool
     */
    public function isValidToken($token)
    {
        if (!$token) {
            return false;
        }

        $defaultToken = $this->scopeConfig->getValue(self::XML_PATH_WOOCOMMERCE_TOKEN);
        if ($defaultToken && hash_equals((string) $defaultToken, (string) $token)) {
            return true;
        }

        // Fall back to scoped overrides — also covers the case where there's
        // no default token at all and only website/store-scoped tokens exist.
        return $this->getWebsiteIdFromToken($token) !== null;
    }
}

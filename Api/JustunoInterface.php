<?php
namespace Justuno\M2\Api;

interface JustunoInterface
{
    /**
     * Get products
     *
     * @param string $date
     * @param int $limit
     * @param int $page
     * @param int|null $site_id Optional Magento website ID to filter by. If
     *        omitted, the website is derived from the auth token's config
     *        scope (see readme for multi-store setup).
     * @return array
     */
    public function getProducts($date = null, $limit = 20, $page = 1, $site_id = null);

    /**
     * Get orders
     *
     * @param string $date
     * @param int $limit
     * @param int $page
     * @param string $created_at_min
     * @param int|null $site_id Optional Magento website ID to filter by. If
     *        omitted, the website is derived from the auth token's config
     *        scope (see readme for multi-store setup).
     * @return array
     */
    public function getOrders($date = null, $limit = 20, $page = 1, $created_at_min = null, $site_id = null);
    /**
     * Get cart data
     *
     * @return array
     */
    public function getCartData();

    /**
     * Apply discount code
     *
     * @param string $code
     * @return bool
     */
    public function applyDiscountCode($code);
}

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
     * @return array
     */
    public function getProducts($date = null, $limit = 20, $page = 1);

    /**
     * Get orders
     *
     * @param string $date
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getOrders($date = null, $limit = 20, $page = 1);

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
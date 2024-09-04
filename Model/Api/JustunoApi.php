<?php
namespace Justuno\M2\Model\Api;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\UrlInterface;

use Justuno\M2\Api\JustunoInterface;

class JustunoApi implements JustunoInterface
{
    protected $productRepository;
    protected $searchCriteriaBuilder;
    protected $orderRepository;
    protected $storeManager;
    protected $imageHelper;
    protected $pricingHelper;
    protected $urlBuilder;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreManagerInterface $storeManager,
        Image $imageHelper,
        PricingHelper $pricingHelper,
        UrlInterface $urlBuilder
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->pricingHelper = $pricingHelper;
        $this->urlBuilder = $urlBuilder;
    }

    public function getProducts($date = null, $limit = 20, $page = 1)
    {
        $this->searchCriteriaBuilder->setPageSize($limit);
        $this->searchCriteriaBuilder->setCurrentPage($page);

        if ($date) {
            $this->searchCriteriaBuilder->addFilter('updated_at', $date, 'gteq');
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria);

        $result = [];
        foreach ($products->getItems() as $product) {
            $result[] = $this->formatProduct($product);
        }

        return $result;
    }

    private function formatProduct($product)
    {
        $pricing = $this->getPricing($product);
        $images = $this->getProductImages($product);
        $options = $this->getProductOptions($product);
        $variationsData = $this->getProductVariations($product);
        $variations = $variationsData['variations'];
        $variationPriceInfo = $variationsData['cheapestVariant'];
        
        $price = $pricing["Price"];
        $msrp = $pricing["MSRP"];
        $salePrice = $pricing["SalePrice"];
        if ($product->getTypeId() === 'configurable' && $variationPriceInfo !== null) {
            $price = $variationPriceInfo['price'];
            $msrp = $variationPriceInfo['msrp'];
            $salePrice = $variationPriceInfo['salePrice'];
        }

        return [
            "ID" => (string) $product->getId(),
            "MSRP" => $msrp,
            "Price" => $price,
            "SalePrice" => $salePrice,
            "Title" => $product->getName(),
            "ImageURL1" => isset($images[0]) ? $images[0] : null,
            "ImageURL2" => isset($images[1]) ? $images[1] : null,
            "ImageURL3" => isset($images[2]) ? $images[2] : null,
            "AddToCartURL" => $product->getTypeId() !== "configurable" ? $this->getAddToCartUrl($product) : null,
            "URL" => $product->getProductUrl(),
            "OptionType1" => isset($options[0]) ? $options[0] : null,
            "OptionType2" => isset($options[1]) ? $options[1] : null,
            "OptionType3" => isset($options[2]) ? $options[2] : null,
            "Categories" => $this->getProductCategories($product),
            "Tags" => $this->getProductTags($product),
            "CreatedAt" => $product->getCreatedAt(),
            "UpdatedAt" => $product->getUpdatedAt(),
            "ReviewsCount" => $product->getReviewCount(),
            "ReviewsRatingAvg" => floatval($product->getRatingSummary()),
            "Variants" => $variations,
        ];
    }

    private function getPricing($product)
    {
        $regularPrice = $product->getPrice();
        $finalPrice = $product->getFinalPrice();

        return [
            "MSRP" => $this->pricingHelper->currency($regularPrice, false, false),
            "Price" => $this->pricingHelper->currency($regularPrice, false, false),
            "SalePrice" => ($finalPrice < $regularPrice) ? $this->pricingHelper->currency($finalPrice, false, false) : null,
        ];
    }

    private function getProductImages($product)
    {
        $images = [];
        $gallery = $product->getMediaGalleryImages();
        foreach ($gallery as $image) {
            $images[] = $this->imageHelper->init($product, 'product_page_image_large')->setImageFile($image->getFile())->getUrl();
            if (count($images) >= 3) break;
        }
        return $images;
    }

    private function getProductOptions($product)
    {
        $options = [];
        if ($product->getTypeId() === 'configurable') {
            $configurableAttributes = $product->getTypeInstance()->getConfigurableAttributes($product);
            foreach ($configurableAttributes as $attribute) {
                $options[] = $attribute->getProductAttribute()->getAttributeCode();
            }
        }
        return $options;
    }

    private function getProductCategories($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryRepository = $objectManager->get(\Magento\Catalog\Api\CategoryRepositoryInterface::class);
        $categories = [];
        foreach ($product->getCategoryCollection() as $category) {
            $loadedCategory = $categoryRepository->get($category->getId(), 0);
            $categories[] = [
                "ID" => (string) $category->getId(),
                "Name" => $loadedCategory->getName(),
                "Description" => $loadedCategory->getDescription(),
                "URL" => $category->getUrl(),
                "ImageURL" => $this->imageHelper->init($category, 'category_page_grid')->getUrl(),
                "Keywords" => $loadedCategory->getMetaKeywords(),
            ];
        }
        return $categories;
    }

    private function getProductTags($product)
    {
        $tags = [];
        $tagCollection = $product->getTagCollection();
        if ($tagCollection) {
            foreach ($tagCollection as $tag) {
                $tags[] = [
                    "ID" => (string) $tag->getId(),
                    "Name" => $tag->getName(),
                ];
            }
        }
        return $tags;
    }

    private function getProductVariations($product)
    {
        $variations = [];
        $cheapestVariant = null;
        
        if ($product->getTypeId() === 'configurable') {
            $configurableAttributes = $product->getTypeInstance()->getConfigurableAttributes($product);
            $configurableProducts = $product->getTypeInstance()->getUsedProducts($product);
            
            foreach ($configurableProducts as $variationProduct) {
                $options = [];
                
                foreach ($configurableAttributes as $attribute) {
                    $attributeCode = $attribute->getProductAttribute()->getAttributeCode();
                    $optionValue = $variationProduct->getData($attributeCode);
                    $attributeSource = $attribute->getProductAttribute()->getSource();
                    $optionText = $attributeSource->getOptionText($optionValue);
                    $options[] = $optionText;
                }

                $inventoryQuantity = $this->getInventoryQuantity($variationProduct);
                $isAvailable = $inventoryQuantity > 0;
                $finalPrice = $variationProduct->getFinalPrice();
                
                $variationData = [
                    "ID" => (string) $variationProduct->getId(),
                    "Title" => $variationProduct->getName(),
                    "SKU" => $variationProduct->getSku(),
                    "MSRP" => $this->pricingHelper->currency($variationProduct->getPrice(), false, false),
                    "Option1" => isset($options[0]) ? $options[0] : null,
                    "Option2" => isset($options[1]) ? $options[1] : null,
                    "Option3" => isset($options[2]) ? $options[2] : null,
                    "SalePrice" => $this->pricingHelper->currency($finalPrice, false, false),
                    "InventoryQuantity" => $inventoryQuantity,
                ];
                
                $variations[] = $variationData;
                
                if ($isAvailable && ($cheapestVariant === null || $finalPrice < $cheapestVariant['price'])) {
                    $cheapestVariant = [
                        'price' => $finalPrice,
                        'msrp' => $variationProduct->getPrice(),
                        'salePrice' => $finalPrice
                    ];
                }
            }
        } else {
            $inventoryQuantity = $this->getInventoryQuantity($product);
            $isAvailable = $inventoryQuantity > 0;
            $finalPrice = $product->getFinalPrice();
            
            $variations[] = [
                "ID" => (string) $product->getId(),
                "Title" => $product->getName(),
                "SKU" => $product->getSku(),
                "MSRP" => $this->pricingHelper->currency($product->getPrice(), false, false),
                "Option1" => null,
                "Option2" => null,
                "Option3" => null,
                "SalePrice" => $this->pricingHelper->currency($finalPrice, false, false),
                "InventoryQuantity" => $inventoryQuantity,
            ];
            
            if ($isAvailable) {
                $cheapestVariant = [
                    'price' => $finalPrice,
                    'msrp' => $product->getPrice(),
                    'salePrice' => $finalPrice
                ];
            }
        }
        
        return [
            'variations' => $variations,
            'cheapestVariant' => $cheapestVariant ? [
                'price' => $this->pricingHelper->currency($cheapestVariant['price'], false, false),
                'msrp' => $this->pricingHelper->currency($cheapestVariant['msrp'], false, false),
                'salePrice' => $this->pricingHelper->currency($cheapestVariant['salePrice'], false, false)
            ] : null
        ];
    }

    private function getVariationOption($configurable, $variation, $index)
    {
        $attributes = $configurable->getTypeInstance()->getConfigurableAttributes($configurable);
        $attribute = $attributes->getItems()[$index] ?? null;
        if ($attribute) {
            $attributeCode = $attribute->getProductAttribute()->getAttributeCode();
            return $variation->getData($attributeCode);
        }
        return null;
    }

    private function getInventoryQuantity($product)
    {
        if (!$product->getStatus() || !$product->isAvailable()) {
            return -9999; // Product is disabled or not available
        }
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        if (!$stockItem) {
            return 10001; // Default to always in stock if no stock item
        }
    
        if (!$stockItem->getManageStock()) {
            return 10001; // Not tracking inventory, so always in stock
        }
    
        $qty = $stockItem->getQty();
    
        if ($stockItem->getIsInStock()) {
            return max($qty, 0); // Return actual quantity, but not less than 0
        }
    
        return 10001;
    }

    private function getAddToCartUrl($product)
    {
        return $this->urlBuilder->getUrl('checkout/cart/add', ['product' => $product->getId()]);
    }

    public function getOrders($date = null, $limit = 20, $page = 1)
    {
        $this->searchCriteriaBuilder->setPageSize($limit);
        $this->searchCriteriaBuilder->setCurrentPage($page);

        if ($date) {
            $this->searchCriteriaBuilder->addFilter('updated_at', $date, 'gteq');
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $orders = $this->orderRepository->getList($searchCriteria);

        $result = [];
        foreach ($orders->getItems() as $order) {
            $result[] = $this->formatOrder($order);
        }

        return $result;
    }
    private function formatOrder($order)
    {
        $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $variantId = null;
            if ($item->getProductType() == 'configurable') {
                $simpleProduct = $item->getProductOptionByCode('simple_product');
                $variantId = $simpleProduct ? (string) $simpleProduct->getProductId() : null;
            } else {
                $variantId = (string) $item->getProductId();
            }
    
            $items[] = [
                "ProductID" => (string) $item->getProductId(),
                "OrderID" => (string) $order->getId(),
                "VariantID" => $variantId,
                "Price" => floatval($item->getPrice()),
                "TotalDiscount" => floatval($item->getDiscountAmount()),
            ];
        }

        return [
            "ID" => (string) $order->getId(),
            "OrderNumber" => $order->getIncrementId(),
            "CustomerID" => (string) $order->getCustomerId(),
            "Email" => $order->getCustomerEmail(),
            "CreatedAt" => $order->getCreatedAt(),
            "UpdatedAt" => $order->getUpdatedAt(),
            "TotalPrice" => floatval($order->getGrandTotal()),
            "SubtotalPrice" => floatval($order->getSubtotal()),
            "ShippingPrice" => floatval($order->getShippingAmount()),
            "TotalTax" => floatval($order->getTaxAmount()),
            "TotalDiscounts" => floatval($order->getDiscountAmount()),
            "TotalItems" => count($items),
            "Currency" => $order->getOrderCurrencyCode(),
            "Status" => $order->getStatus(),
            "IP" => $order->getRemoteIp(),
            "CountryCode" => $order->getBillingAddress() ? $order->getBillingAddress()->getCountryId() : null,
            "LineItems" => $items,
            "Customer" => $this->formatCustomer($order),
        ];
    }

    private function formatCustomer($order)
    {
        return [
            "ID" => (string) $order->getCustomerId(),
            "Email" => $order->getCustomerEmail(),
            "CreatedAt" => $order->getCustomerCreatedAt(),
            "UpdatedAt" => $order->getUpdatedAt(),
            "FirstName" => $order->getCustomerFirstname(),
            "LastName" => $order->getCustomerLastname(),
            "OrdersCount" => $this->getCustomerOrdersCount($order->getCustomerId()),
            "TotalSpend" => $this->getCustomerTotalSpend($order->getCustomerId()),
            "Tags" => null, // Magento doesn't have a built-in customer tagging system
            "Currency" => $order->getOrderCurrencyCode(),
            "Address1" => $order->getBillingAddress() ? $order->getBillingAddress()->getStreetLine(1) : null,
            "Address2" => $order->getBillingAddress() ? $order->getBillingAddress()->getStreetLine(2) : null,
            "City" => $order->getBillingAddress() ? $order->getBillingAddress()->getCity() : null,
            "Zip" => $order->getBillingAddress() ? $order->getBillingAddress()->getPostcode() : null,
            "ProvinceCode" => $order->getBillingAddress() ? $order->getBillingAddress()->getRegionCode() : null,
            "CountryCode" => $order->getBillingAddress() ? $order->getBillingAddress()->getCountryId() : null,
        ];
    }

    private function getCustomerOrdersCount($customerId)
    {
        if (!$customerId) return 1;

        $this->searchCriteriaBuilder->addFilter('customer_id', $customerId);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $orders = $this->orderRepository->getList($searchCriteria);
        return $orders->getTotalCount();
    }

    private function getCustomerTotalSpend($customerId)
    {
        if (!$customerId) return 0;

        $this->searchCriteriaBuilder->addFilter('customer_id', $customerId);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $orders = $this->orderRepository->getList($searchCriteria);
        
        $totalSpend = 0;
        foreach ($orders->getItems() as $order) {
            $totalSpend += $order->getGrandTotal();
        }
        return $totalSpend;
    }

    public function getCartData()
    {
        // This method should be implemented in a separate class that handles the current session's cart
        // For demonstration purposes, we'll return a placeholder
        return [
            'total' => 0,
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'currency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
            'items' => [],
        ];
    }

    public function applyDiscountCode($code)
    {
        // This method should be implemented in a separate class that handles the current session's cart
        // For demonstration purposes, we'll return a placeholder
        return false;
    }
}

<?php
namespace Justuno\M2\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Justuno\M2\Helper\Data as JustunoHelper;

class ProductView implements ObserverInterface
{
    protected $justunoHelper;

    public function __construct(JustunoHelper $justunoHelper)
    {
        $this->justunoHelper = $justunoHelper;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $accountNumber = $this->justunoHelper->getApiKey();

        $data = [
            'productId' => $product->getId(),
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'price' => $product->getFinalPrice(),
            'url' => $product->getProductUrl()
        ];

        $this->addJustunoScript('productView', $data, $accountNumber);
    }

    private function addJustunoScript($eventType, $data, $accountNumber)
    {
        $jsonData = json_encode($data);
        $script = "
        <script>
            if (typeof ju4app !== 'undefined') {
                ju4app('track', '{$eventType}', {$jsonData});
            }
        </script>
        ";
        
        // In a real-world scenario, you would inject this script into the page
        // For demonstration purposes, we're just storing it in the registry
        \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Registry::class)
            ->register('justuno_script', $script, true);
    }
}
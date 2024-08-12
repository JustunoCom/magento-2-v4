<?php
namespace Justuno\M2\Block;

use Magento\Framework\View\Element\Template;
use Justuno\M2\Helper\Data as JustunoHelper;
use Magento\Framework\Registry;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Script extends Template
{
    protected $justunoHelper;
    protected $registry;
    protected $customerSessionFactory;
    protected $storeManager;

    public function __construct(
        Template\Context $context,
        JustunoHelper $justunoHelper,
        Registry $registry,
        CustomerSessionFactory $customerSessionFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->justunoHelper = $justunoHelper;
        $this->registry = $registry;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getApiKey()
    {
        return $this->justunoHelper->getApiKey();
    }

    public function getSubDomain()
    {
        return $this->justunoHelper->getSubDomain();
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSessionFactory->create()->isLoggedIn();
    }

    public function getCustomerEmail()
    {
        if ($this->isCustomerLoggedIn()) {
            return $this->customerSessionFactory->create()->getCustomer()->getEmail();
        }
        return null;
    }
    
    public function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }
}
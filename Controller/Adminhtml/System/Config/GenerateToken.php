<?php
namespace Justuno\M2\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Math\Random;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class GenerateToken extends Action
{
    protected $resultJsonFactory;
    protected $configWriter;
    protected $mathRandom;
    protected $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        WriterInterface $configWriter,
        Random $mathRandom,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configWriter = $configWriter;
        $this->mathRandom = $mathRandom;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $token = $this->mathRandom->getRandomString(32);

            // Persist the token at the same scope the admin is currently
            // editing — `default`, a website, or a store. This is what makes
            // per-site tokens (and therefore per-site product/order feeds)
            // possible on multi-store installations.
            list($scope, $scopeId) = $this->resolveScope();
            $this->configWriter->save(
                'justuno/general/woocommerce_token',
                $token,
                $scope,
                $scopeId
            );

            return $result->setData([
                'success' => true,
                'token' => $token,
                'scope' => $scope,
                'scope_id' => $scopeId,
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Determine the config scope (default / websites / stores) being edited
     * from the admin URL parameters. Magento may pass either numeric IDs or
     * codes; resolve both.
     *
     * @return array{0:string,1:int}
     */
    private function resolveScope()
    {
        $request = $this->getRequest();
        $storeParam = $request->getParam('store');
        $websiteParam = $request->getParam('website');

        if ($storeParam) {
            try {
                $store = $this->storeManager->getStore($storeParam);
                return [ScopeInterface::SCOPE_STORES, (int) $store->getId()];
            } catch (\Exception $e) {
                // fall through
            }
        }
        if ($websiteParam) {
            try {
                $website = $this->storeManager->getWebsite($websiteParam);
                return [ScopeInterface::SCOPE_WEBSITES, (int) $website->getId()];
            } catch (\Exception $e) {
                // fall through
            }
        }

        return [ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0];
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Justuno_M2::config');
    }
}

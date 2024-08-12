<?php
namespace Justuno\M2\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Math\Random;

class GenerateToken extends Action
{
    protected $resultJsonFactory;
    protected $configWriter;
    protected $mathRandom;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        WriterInterface $configWriter,
        Random $mathRandom
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configWriter = $configWriter;
        $this->mathRandom = $mathRandom;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $token = $this->mathRandom->getRandomString(32);
            $this->configWriter->save('justuno/general/woocommerce_token', $token);

            return $result->setData([
                'success' => true,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Justuno_M2::config');
    }
}
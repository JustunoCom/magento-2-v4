<?php
namespace Justuno\M2\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class GenerateToken extends Field
{
    protected $configWriter;

    public function __construct(
        Context $context,
        WriterInterface $configWriter,
        array $data = []
    ) {
        $this->configWriter = $configWriter;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $html .= $this->getButtonHtml();
        $html .= $this->getScriptHtml();
        return $html;
    }

    private function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData([
            'id' => 'generate_token',
            'label' => __('Generate Token'),
        ]);

        return $button->toHtml();
    }

    private function getScriptHtml()
    {
        // Forward the scope (website/store) the admin is currently editing so
        // the controller can save the new token at that scope. Without this,
        // multi-site installs end up sharing one token at the default scope
        // and product/order feeds can't be segregated.
        $request = $this->getRequest();
        $queryParams = [];
        if ($websiteCode = $request->getParam('website')) {
            $queryParams['website'] = $websiteCode;
        }
        if ($storeCode = $request->getParam('store')) {
            $queryParams['store'] = $storeCode;
        }
        $adminUrl = $this->getUrl('justuno/system_config/generatetoken', $queryParams);
        return <<<SCRIPT
<script type="text/javascript">
    require(['jquery'], function($) {
        $('#generate_token').click(function() {
            $.ajax({
                url: '{$adminUrl}',
                type: 'POST',
                dataType: 'json',
                data: {
                    form_key: FORM_KEY
                },
                showLoader: true,
                success: function(response) {
                    if (response.success) {
                        $('#justuno_general_woocommerce_token').val(response.token);
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            });
        });
    });
</script>
SCRIPT;
    }
}
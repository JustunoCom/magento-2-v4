<?php
namespace Justuno\M2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SubDomain implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'justone.ai', 'label' => __('justone.ai')],
        ];
    }
}
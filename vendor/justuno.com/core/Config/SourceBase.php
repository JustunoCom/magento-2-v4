<?php
namespace Justuno\Core\Config;
use Magento\Framework\DataObject as _P;
use Magento\Framework\Option\ArrayInterface;
/**
 * 2017-03-28
 * This class should be a descendant of @see \Magento\Framework\DataObject to retrieve the `path` property value:
 * @see \Justuno\Core\Config\Source::setPath()
 * @see \Magento\Config\Model\Config\Structure\Element\Field::_getOptionsFromSourceModel()
 *		$sourceModel = $this->_sourceFactory->create($sourceModel);
 *		if ($sourceModel instanceof \Magento\Framework\DataObject) {
 *			$sourceModel->setPath($this->getPath());
 *		}
 * https://github.com/magento/magento2/blob/2.1.5/app/code/Magento/Config/Model/Config/Structure/Element/Field.php#L435-L438
 * 2020-08-22 "Port the `Df\Config\SourceBase` class" https://github.com/justuno-com/core/issues/259
 * @see \Justuno\Core\Config\Source
 */
abstract class SourceBase extends _P implements ArrayInterface {
	/**
	 * 2019-06-11
	 * @see \Magento\Eav\Model\Entity\Attribute\Source\SourceInterface::getAllOptions() 
	 * @see \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource::toOptionArray():
	 *		public function toOptionArray() {
	 *			return $this->getAllOptions();
	 *		}
	 * @used-by \Magento\Customer\Model\AttributeMetadataConverter::createMetadataAttribute():
	 *		$options = [];
	 *	 	if ($attribute->usesSource()) {
	 *			foreach ($attribute->getSource()->getAllOptions() as $option) { 
	 * https://github.com/magento/magento2/blob/2.3.1/app/code/Magento/Customer/Model/AttributeMetadataConverter.php#L66-L68
	 * @param bool $withEmpty [optional]
	 * @return array(array('label' => string, 'value' => int|string))
	 */
	final function getAllOptions($withEmpty = true) {return $this->toOptionArray();}
}
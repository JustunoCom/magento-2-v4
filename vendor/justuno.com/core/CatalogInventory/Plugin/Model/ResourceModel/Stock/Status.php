<?php
namespace Justuno\Core\CatalogInventory\Plugin\Model\ResourceModel\Stock;
use Justuno\Core\InventoryCatalog\Plugin\Model\ResourceModel\AddStockDataToCollection as AddStockData;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as Sb;
use Magento\Catalog\Model\ResourceModel\Product\Collection as C;
/**
 * 2020-11-23
 * @see \Justuno\Core\InventoryCatalog\Plugin\Model\ResourceModel\AddStockDataToCollection
 * 1) "Add an ability to preserve out of stock (including just disabled) products in a collection
 * despite of the `cataloginventory/options/show_out_of_stock` option's value": https://github.com/mage2pro/core/issues/148
 * 2) The subject class @see \Magento\CatalogInventory\Model\ResourceModel\Stock\Status class
 * is deprecated since Magento 2.3: https://github.com/magento/magento2/blob/2.3.0/app/code/Magento/CatalogInventory/Model/ResourceModel/Stock/Status.php#L17
 * That is why I implemented another plugin
 * @see \Justuno\Core\InventoryCatalog\Plugin\Model\ResourceModel\AddStockDataToCollection
 * to the @see \Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection class.
 * Currently (in Magento 2.4.0) a @see \Magento\CatalogInventory\Model\ResourceModel\Stock\Status::addStockDataToCollection()
 * call is intercepted by the @see \Magento\InventoryCatalog\Plugin\CatalogInventory\Model\ResourceModel\Stock\Status\AdaptAddStockDataToCollectionPlugin::aroundAddStockDataToCollection() plugin,
 * which replaces the @see \Magento\CatalogInventory\Model\ResourceModel\Stock\Status::addStockDataToCollection() implementation
 * with a @see \Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection::execute() call.
 * Nevertheless, I still implemented plugins to the both old and new implementations
 * because somobody could call @see \Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection::execute() directly,
 * and my plugin to @see \Magento\CatalogInventory\Model\ResourceModel\Stock\Status::addStockDataToCollection()
 * will not be triggered in this case.
 */
final class Status {
	/**
	 * 2020-11-23
	 * @see \Magento\CatalogInventory\Model\ResourceModel\Stock\Status::addStockDataToCollection()
	 * @param Sb $sb
	 * @param C $c
	 * @param bool $skipAbsent
	 * @return mixed[]
	 */
	function beforeAddStockDataToCollection(Sb $sb, C $c, $skipAbsent) {return [
		$c, $skipAbsent && !$c->getFlag(AddStockData::PRESERVE_ABSENT)
	];}
}
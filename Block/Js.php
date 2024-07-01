<?php
namespace Justuno\M2\Block;
use Justuno\M2\Settings as S;
use Magento\Framework\View\Element\AbstractBlock as _P;
use Magento\Sales\Model\Order as O;
# 2019-11-15
/** @final Unable to use the PHP «final» keyword here because of the M2 code generation. */
class Js extends _P {
	/**
	 * 2019-11-15
	 * @override
	 * @see _P::_toHtml()
	 * @used-by _P::toHtml():
	 *		$html = $this->_loadCache();
	 *		if ($html === false) {
	 *			if ($this->hasData('translate_inline')) {
	 *				$this->inlineTranslation->suspend($this->getData('translate_inline'));
	 *			}
	 *			$this->_beforeToHtml();
	 *			$html = $this->_toHtml();
	 *			$this->_saveCache($html);
	 *			if ($this->hasData('translate_inline')) {
	 *				$this->inlineTranslation->resume();
	 *			}
	 *		}
	 *		$html = $this->_afterToHtml($html);
	 * https://github.com/magento/magento2/blob/2.2.0/lib/internal/Magento/Framework/View/Element/AbstractBlock.php#L643-L689
	 */
	protected function _toHtml():string { /** @var string $r */
		if (!ju_is_guid($id = S::s()->accid())) {
			$r = '';
		}
		else {
			$p = ju_clean([
                # 2022-07-16 "Implement the «Custom Subdomain» field": https://github.com/JustunoCom/magento-2-v4/issues/2
                'domain' => S::s()->domain()
                ,'merchantId' => $id
            ]); /** @var array(string => mixed) $p */
			if (ju_is_catalog_product_view()) {
				$p += ['action' => ju_action_name(), 'productId' => ju_product_current_id()];
			}
			elseif (ju_is_checkout_success()) {
				$o = ju_order_last(); /** @var O $o */
				$p += ['currency' => $o->getOrderCurrencyCode(), 'orderId' => $o->getId(), 'order' => [
					'currency' => $o->getOrderCurrencyCode()
					,'discount' => $o->getDiscountAmount()
					,'discountCodes'=> [$o->getCouponCode()]
					,'grandTotal' => $o->getGrandTotal()
					,'orderID' =>  $o->getId()
					,'shipping' => $o->getShippingAmount()
					,'subtotal' => $o->getSubtotal()
					,'tax' => $o->getTaxAmount()
				]];
			}
			$r = ju_js(__CLASS__, '', $p);
		}
		return $r;
	}
}

<?php
namespace Justuno\M2;
/**
 * 2021-03-06
 * @method static Settings s()
 */
class Settings extends \Justuno\Core\Config\Settings {
	/**
	 * 2021-03-06
	 * @used-by \Justuno\M2\Block\Js::_toHtml()
	 */
	function accid():string {return (string)$this->v();}

	/**
	 * 2021-03-06
	 * @used-by \Justuno\M2\Controller\Response\Catalog::execute()
	 */
	function brand_attribute():string {return (string)$this->v();}

	/**
	 * 2022-07-13 "Implement the «Custom Subdomain» field": https://github.com/JustunoCom/magento-2-v4/issues/2
	 * @used-by \Justuno\M2\Block\Js::_toHtml()
	 */
	function domain():string {return $this->v();}

	/**
	 * 2021-03-06
	 * @override
	 * @see \Justuno\Core\Config\Settings::prefix()
	 * @used-by \Justuno\Core\Config\Settings::v()
	 */
	protected function prefix():string {return 'justuno_settings/options_interface';}
}

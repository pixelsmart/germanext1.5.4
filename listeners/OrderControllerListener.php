<?php
class OrderControllerListener extends Germanext
{
	public function __construct(){}
	
	public function execute($context)
	{
		$context->smarty->assign(
			array(
				'ONLY_SHIPPING_CART'   => Tools::getValue('shipping_cart')
			)
		);
		
		if (Configuration::get('GN_CHECK_PAYMENT')) {
			$context->controller->addJs($this->_path . 'js/order.js');
		}
	}
}

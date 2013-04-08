<?php
class gn_cashondelivery extends GN_PaymentManager
{
	public function __construct()
	{
		$this->_dir = 'cashondelivery';
		
		parent::__construct();
		
		$this->_publicName = $this->l('Cash On Delivery');
		$this->_publicDescription = $this->l('Pay with Cashondelivery');
	}
	
	public function presentPayment($params)
	{
		global $smarty;
		
		return parent::presentPayment($params);
	}
   
	public function callPayment($params)
	{
        $context = Context::getContext();
		Tools::redirect($context->link->getModuleLink('cashondelivery', 'validation', array('confirm' => '1'), true));
	}
}

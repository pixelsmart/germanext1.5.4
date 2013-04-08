<?php
class gn_bill extends GN_PaymentManager
{
	public function __construct()
	{
		$this->_dir = 'bill';
		
		parent::__construct();
		
		$this->_publicName = $this->l('Bill');
		$this->_publicDescription = $this->l('Pay using bill (order process will be longer)');
	}
	
	private function checkIfApplicable()
	{
		global $cart;
		
		$ordersToAllow = (int)Configuration::get('PS_BILL_NUM');
		$sumAllow      = (float)Configuration::get('PS_BILL_SUM');
		$cartTotal     = (float)$cart->getOrderTotal(true, 4);
		
		if (Validate::isLoadedObject($customer = new Customer((int)$cart->id_customer)))
		{
			$customerData  = $customer->getStats();
			
			$customerOrders = array_key_exists('nb_orders', $customerData) ? (int)$customerData['nb_orders'] : 0;
			
			if ($customerOrders < $ordersToAllow)
				return false;
		}
		
		if ($cartTotal > $sumAllow)
			return false;
		
		return true;
	}
	
	public function presentPayment($params)
	{
		global $smarty;
		
		if ($this->checkIfApplicable())
			return parent::presentPayment($params);
		
		return false;
	}
   
	public function callPayment($params)
	{
		global $cookie, $cart;
		
		if ($this->checkIfApplicable())
		{
			if ( ! isset($cookie->id_currency))
				$cookie->id_currency = $cart->id_currency;

            $context = Context::getContext();
            Tools::redirect($context->link->getModuleLink('bill', 'validation', array(), true).'&confirm=1');
		}
		
		return false;
	}
}

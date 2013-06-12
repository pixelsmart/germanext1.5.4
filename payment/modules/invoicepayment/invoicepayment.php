<?php
class gn_invoicepayment extends GN_PaymentManager
{
	public function __construct() {
		$this->_dir = 'invoicepayment';
		
		parent::__construct();
		
		$this->_publicName = $this->l('Invoice Payment');
		$this->_publicDescription = $this->l('Pay using bill (order process will be longer)');
	}
	
	private function checkIfApplicable($cart) {
		$instance = Module::getInstanceByName($this->_dir);
		
		return $instance->checkCart($cart);
	}
	
	public function presentPayment($params) {
		if ($this->checkIfApplicable(Context::getContext()->cart)) {
			return parent::presentPayment($params);
		}
		
		return false;
	}
   
	public function callPayment($context) {
		if ($this->checkIfApplicable($context->cart)) {
			if ( ! isset($context->cookie->id_currency)) {
				$context->cookie->id_currency = $context->cart->id_currency;
			}

			Tools::redirect($context->link->getModuleLink('invoicepayment', 'validation', array('confirm' => 1), true));
		}
		
		return false;
	}
}

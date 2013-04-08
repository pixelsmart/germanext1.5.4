<?php
class gn_bankwire extends GN_PaymentManager
{
	public function __construct()
	{
		$this->_dir = 'bankwire';
		
		parent::__construct();
		
		$this->_publicName = $this->l('Bankwire');
		$this->_publicDescription = $this->l('Pay by bank wire (order process will be longer)');
	}
   
	public function callPayment($context)
	{
		if ( ! isset($context->cookie->id_currency))
		{
			$context->cookie->id_currency = (int)$context->cart->id_currency;
		}
		
		Tools::redirect($context->link->getModuleLink('bankwire', 'validation'));
	}
}

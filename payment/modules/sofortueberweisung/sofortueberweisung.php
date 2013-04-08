<?php
class gn_sofortueberweisung extends GN_PaymentManager
{
	public function __construct()
	{
		$this->_dir = 'sofortueberweisung';
		
		parent::__construct();
		
		$this->_publicName = $this->l('Sofortüberweisung');
		$this->_publicDescription = $this->l('Pay with Sofortüberweisung');
	}

    public function presentPayment($params)
    {
        global $smarty;

        return parent::presentPayment($params);
    }

    public function callPayment($context)
    {
        Tools::redirect($context->smarty->tpl_vars['base_dir_ssl']->value.'modules/sofortueberweisung/redirect.php');
    }
}

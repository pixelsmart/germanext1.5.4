<?php
class gn_paypal extends GN_PaymentManager
{
	public function __construct()
	{
		$this->_dir = 'paypal';
		
		parent::__construct();
		
		$this->_publicName = $this->l('PayPal');
		$this->_publicDescription = $this->l('Pay with PayPal');
	}

    public function presentPayment($params)
    {
        global $smarty;

        return parent::presentPayment($params);

    }

    public function callPayment($context)
    {
        if ( ! isset($context->cookie->id_currency))
            $context->cookie->id_currency = $context->cart->id_currency;
			
        Tools::redirect(Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/paypal/express_checkout/payment.php?express_checkout=payment_cart&current_shop_url='.Tools::getShopDomainSsl(true, true).$_SERVER['REQUEST_URI']);

        return false;
    }
}

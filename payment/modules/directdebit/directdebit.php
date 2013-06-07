<?php
class gn_directdebit extends GN_PaymentManager
{
    public function __construct()
    {
        $this->_dir = 'directdebit';

        parent::__construct();

        $this->_publicName = $this->l('DirectDebit');
        $this->_publicDescription = $this->l('Pay using DirectDebit');
    }



    public function presentPayment($params)
    {
        global $smarty;

        return parent::presentPayment($params);

    }

    public function ajaxDataPrompt()
    {
        global $cookie, $cart, $smarty;

        return $smarty->fetch(dirname(__FILE__) . '/payment.tpl');
    }

    public function callPayment($params)
    {
        global $cookie, $cart;

        if ( ! isset($cookie->id_currency))
            $cookie->id_currency = $cart->id_currency;

        $getStr = '&';


        foreach ($_POST as $var => $value)
            $getStr.= urlencode($var) . '=' . urlencode($value) . '&';

        $getStr = rtrim($getStr, '&');

        $context = Context::getContext();
		//print_r($context);
		//echo $context->link->getModuleLink('directdebit', 'payment', array(), true); exit;
		Tools::redirect(Tools::getShopDomain(true, true).__PS_BASE_URI__.'index.php?fc=module&module=directdebit&controller=payment&paymentSubmit=1' . (strlen($getStr) > 1 ? $getStr : null));


        return false;
    }
}

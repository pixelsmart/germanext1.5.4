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

    public function ajaxDataPrompt($context)
    {
        return $context->smarty->fetch(dirname(__FILE__) . '/payment.tpl');
    }

    public function callPayment($context)
    {
        if ( ! isset($context->cookie->id_currency))
            $context->cookie->id_currency = $context->cart->id_currency;

        $getStr = '&';


        foreach ($_POST as $var => $value)
            $getStr.= urlencode($var) . '=' . urlencode($value) . '&';

        $getStr = rtrim($getStr, '&');

        Tools::redirect($context->link->getModuleLink('directdebit', 'payment', array(), true).'&paymentSubmit=1' . (strlen($getStr) > 1 ? $getStr : null));


        return false;
    }
}

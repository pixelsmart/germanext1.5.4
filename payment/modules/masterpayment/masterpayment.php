<?php


class gn_masterpayment extends GN_PaymentManager
{
	public function __construct()
	{
		$this->_dir = 'masterpayment';
		
		parent::__construct();
		
		$this->_publicName = $this->l('MasterPayment');
		$this->_publicDescription = $this->l('Pay by MasterPayment');
	}
   
	public function callPayment($params)
	{
		global $cookie, $cart;
			if (!isset($cookie->id_currency))
				$cookie->id_currency = $cart->id_currency;

        $instance = Module::getInstanceByName('masterpayment');

        if ( ! Validate::isLoadedObject($instance))
            return ;

        $context = Context::getContext();
        $cart = $context->cart;
        $link = $context->link;
        $cookie = $context->cookie;
        $instance->module = $instance;

        //Payment methods
        $payment_method = Tools::getValue('payment_method', 'none');
        $payment_methods = $instance->module->getPaymentMethods();

        if(!isset($payment_methods[$payment_method]))
            Tools::redirect('index.php?controller=order');

        $payment_method_name = $payment_methods[$payment_method];

        //Currency
        $currency = Currency::getCurrent();
        $valid_currencies = $instance->module->getValidCurrencies();
        $shop_currencies = Currency::getCurrencies();
        $currencies = array();

        foreach($shop_currencies as $c)
            if(in_array($c['iso_code'], $valid_currencies))
                array_push($currencies, $c['name']);

        //Price
        $totalAmount = $cart->getOrderTotal() + $cart->getOrderTotal(true, Cart::ONLY_PAYMENT);

        //Module configurations
        $cfg = $instance->module->getConfigurations();


        if((bool)$cfg['MP_ORDER_CREATE'])
        {
            //Create order
            $instance->module->validateOrder
            (
                $cart->id,
                Configuration::get('PS_OS_MASTERPAYMENT'),
                $totalAmount,
                $instance->module->displayName,//$payment_method_name,
                $instance->module->l('Payment method').': '.$payment_method_name,
                array(), //$extraVars
                $currency->id,
                false,
                $cart->secure_key
            );
        }


        $order = (int)$instance->module->currentOrder ? new Order($instance->module->currentOrder) : null;
        $customer = new Customer((int)$cart->id_customer);
        $address = new Address((int)$cart->id_address_invoice);


        //Language
        $language = strtoupper(Language::getIsoById($cookie->id_lang));
        //if language not found use default language
        if(!in_array($language, array_keys($instance->module->getValidLanguages())))
            $language = $cfg['MP_LANGUAGE'];

        //URL's
        $order_confirmation_url = $link->getPageLink('order-confirmation.php').'?id_cart='.(int)$cart->id.'&id_module='.(int)$instance->module->id.'&key='.$customer->secure_key;
        $order_validation_url = Tools::getShopDomain(true, true).__PS_BASE_URI__.'modules/masterpayment/'.'validation.php';

        require_once(dirname(__FILE__).'/../../../../masterpayment/lib/api.php');
        //MasterPayment API
        $api = new MasterPaymentApi();
        $api->iframeMode = ($cfg['MP_MODE'] == 'iframe');
        $api->merchantName = $cfg['MP_MERCHANT_NAME'];
        $api->secretKey = $cfg['MP_SECRET_KEY'];
        $api->txId = MasterPayment::encodeTxID($cart);
        $api->orderId = $instance->module->currentOrder;
        $api->basketDescription = str_replace(array('{order}', '{cart}', '{shop}'), array($instance->module->currentOrder, $cart->id, Configuration::get('PS_SHOP_NAME')), $order ? $instance->module->l('Shopping order #{order} - {shop}') : $instance->module->l('Shopping cart #{cart} - {shop}'));
        $api->basketValue = Tools::ps_round($totalAmount, 2) * 100;
        $api->currency = $currency->iso_code;
        $api->language = $language;
        $api->paymentType = $payment_method;
        $api->gatewayStyle = $cfg['MP_GATEWAY_STYLE'];
        $api->UrlPatternSuccess = $order_validation_url;
        $api->UrlPatternFailure = $order_validation_url;
        $api->UrlRedirectSuccess = $order_confirmation_url;
        $api->UrlRedirectFailure = $order_confirmation_url;
        $api->UrlRedirectCancel = $link->getPageLink('order.php').'?step=3';
        $api->showCancelOption = (int)$cfg['MP_CANCEL_OPTION'];

        $api->userId = $customer->id;
        $api->sex = ($customer->id_gender == 9) ? 'unknown' : ($customer->id_gender == 1) ? 'man' : 'woman';
        $api->firstname = $customer->firstname;
        $api->lastname = $customer->lastname;
        $api->email = $customer->email;
        $api->street = $address->address1 .' '. $address->address2;
        $api->zipCode = $address->postcode;
        $api->city = $address->city;
        $api->country = Country::getIsoById($address->id_country);
        $api->birthdate = $customer->birthday;
        $api->mobile = $address->phone ? $address->phone : $address->phone_mobile;

        $api->installmentsCount = $cfg['MP_INSTALLMENTS_COUNT'];
        $api->recurrentPeriod = $cfg['MP_RECURRENT_PERIOD'];
        $api->paymentDelay = $cfg['MP_PAYMENT_DELAY'];
        $api->dueDays = $cfg['MP_DUE_DAYS'];
        $api->invoiceNo = $order ? Configuration::get('PS_INVOICE_PREFIX').$order->invoice_number : '';
        $api->createAsPending = 1;

        if($cfg['MP_INSTALLMENTS_PERIOD'] == 'use_freq')
            $api->installmentsFreq = $cfg['MP_INSTALLMENTS_FREQ'];
        else
            $api->installmentsPeriod = $cfg['MP_INSTALLMENTS_PERIOD'];

        $context->smarty->assign('params', $api->getParams());

        include(dirname(__FILE__) . '/../../../../../header.php');

        if (in_array($currency->iso_code, $valid_currencies)) {
            Tools::addCSS(__PS_BASE_URI__.'modules/masterpayment/views/css/gateway.css');
            echo '<form action="'.$cfg['MP_GATEWAY_URL'].'" method="post" name="masterpayment" '.(($cfg['MP_MODE'] == 'iframe')?'target="masterpayment_gateway_iframe"':'').'>';
            foreach ($api->getParams() as $name => $value) {
                     echo '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
            }
            echo '</form>';

            if ($cfg['MP_MODE'] == 'iframe') echo '<iframe id="masterpayment_gateway_iframe" name="masterpayment_gateway_iframe"></iframe>';

            echo '
            </form>
            <script type="text/javascript">
                    document.masterpayment.submit();
            </script>';
        } else {
            echo '<p class="warning">
                  Chosen currency was not authorized for this payment module!
                  <br />
                  Please select different currency.
                  </p>';
        }

		include(dirname(__FILE__) . '/../../../../../footer.php');
	}

    public function collectPaymentData()
    {
        global $smarty, $cookie, $cart;

        if ( ! Configuration::get('MB_PARAMETERS') ||
            ! Configuration::get('MB_PARAMETERS_2') ||
            (Configuration::get('MB_LOCAL_METHODS') == '' &&
                Configuration::get('MB_INTER_METHODS') == ''))
            return;

        $data = array();
        $flag = false;

        $instance = Module::getInstanceByName('masterpayment');

        if ( ! Validate::isLoadedObject($instance))
            return ;

        $allowedCurrencies = $instance->getCurrency((int)$cart->id_currency);

        foreach ($allowedCurrencies as $allowedCurrency)
            if ($allowedCurrency['id_currency'] == $cart->id_currency)
            {
                $flag = true;

                break;
            }

        if ($flag)
        {

        }

        return sizeof($data) ? $data : null;
    }

    public function presentPaymentOptions($paymentData)
    {
        global $smarty;
        $instance = Module::getInstanceByName('masterpayment');

        if ( ! Validate::isLoadedObject($instance))
            return ;

        //Check if is configured and have valid currency
        if(!Configuration::get('MP_MERCHANT_NAME') || !Configuration::get('MP_SECRET_KEY') || !$instance->getValidCurrency())
            return '';

        $payment_methods = array_intersect_key($instance->getPaymentMethods(), array_flip(explode(',', Configuration::get('MP_PAYMENT_METHODS', array()))));

        $result = array();
        $i = 0;
        foreach ($payment_methods as $code => $method)
        {
            $smarty->assign('embedScript', $i == 0);

            $smarty->assign(array(
                'payment_name'   => $method,
                'payment_option' => $code,
                'payment_image'  => 'modules/masterpayment/views/img/p/'.$code.'.png'
            ));

            $result[] = $smarty->fetch(dirname(__FILE__) . '/masterpayment.tpl');

            $i++;
        }

        return $result;
    }

    public function presentPayment($params)
    {
        parent::presentPayment($params);

        $result = $this->presentPaymentOptions(null);

        return sizeof($result) ? $result : false;
    }
}

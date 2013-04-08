<?php
class gn_moneybookers extends GN_PaymentManager
{
	public function __construct()
	{
		$this->_dir = 'moneybookers';
		$this->_defaultTpl = false;
		
		parent::__construct();
		
		$this->_publicName = $this->l('Moneybookers');
		$this->_publicDescription = $this->l('Pay with moneybookers module');
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
		
		$instance = Module::getInstanceByName('moneybookers');
		
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
			$localMethods = Configuration::get('MB_LOCAL_METHODS');
			$interMethods = Configuration::get('MB_INTER_METHODS');
			
			$data['paymentParams'] = array(
				'display_mode' => (int)(Configuration::get('MB_DISPLAY_MODE')),
				'local' => $localMethods ? explode('|', $localMethods) : array(),
				'inter' => $interMethods ? explode('|', $interMethods) : array(),
				'local_logos' => $instance->_localPaymentMethods,
				'inter_logos' => $instance->_internationalPaymentMethods
			);
            $data['redirect_text'] = $instance->l('Please wait, redirecting to MoneyBookers... Thanks.');
            $data['cancel_text'] = $instance->l('Cancel');
            $data['cart_text'] = $instance->l('My cart');
            $data['return_text'] = $instance->l('Return to shop');
            $data['url'] = Tools::getShopDomain(true, true).__PS_BASE_URI__;



			$address = new Address((int)($cart->id_address_delivery));
			$countryObj = new Country((int)($address->id_country), Configuration::get('PS_LANG_DEFAULT'));
			$customer = new Customer((int)($cart->id_customer));
			$currency = new Currency((int)($cart->id_currency));
			$lang = new Language((int)($cookie->id_lang));

			$mbParams = array();

			$mbParams['pay_to_email'] = Configuration::get('MB_PAY_TO_EMAIL');
			$mbParams['recipient_description'] = Configuration::get('PS_SHOP_NAME');
			$mbParams['hide_login'] = (int)(Configuration::get('MB_HIDE_LOGIN'));
			$mbParams['id_logo'] = (int)(Configuration::get('MB_ID_LOGO'));
			$mbParams['return_url'] = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)($cart->id).'&id_module='.(int)($instance->id).'&key='.$customer->secure_key;
			$mbParams['cancel_url'] = Configuration::get('MB_CANCEL_URL');

			$mbParams['pay_from_email'] = $customer->email;
			$mbParams['firstname'] = $address->firstname;
			$mbParams['lastname'] = $address->lastname;
			$mbParams['address'] = $address->address1;
			$mbParams['address2'] = $address->address2;
			$mbParams['phone_number'] = !empty($address->phone_mobile) ? $address->phone_mobile : $address->phone;
			$mbParams['postal_code'] = $address->postcode;
			$mbParams['city'] = $address->city;
			$mbParams['country'] = isset($instance->_country[strtoupper($countryObj->iso_code)]) ? $instance->_country[strtoupper($countryObj->iso_code)] : '';
			$mbParams['language'] = strtoupper($lang->iso_code);
			$mbParams['date_of_birth'] = substr($customer->birthday, 5, 2).substr($customer->birthday, 8, 2).substr($customer->birthday, 0, 4);

			$mbParams['transaction_id'] = (int)($cart->id).'_'.date('YmdHis').'_'.$cart->secure_key;
			$mbParams['currency'] = $currency->iso_code;
			$mbParams['amount'] = number_format($cart->getOrderTotal(), 2, '.', '');

			$mbParams['status_url'] = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$instance->name.'/validation.php';

			$data['orderParams'] = $mbParams;

		}
		
		return sizeof($data) ? $data : null;
	}
	
	public function presentPaymentOptions($paymentData)
	{
		global $smarty;
		
		$variants = array(
			'inter' => array(
				'logosKey' => 'inter_logos',
				'logosDir' => 'international'
			),
			'local' => array(
				'logosKey' => 'local_logos',
				'logosDir' => 'local'
			)
		);
		
		$translations = array(
			'gir' => $this->l('Giropay (Germany)'),
			'vsa' => $this->l('Visa'),
			'msc' => $this->l('MasterCard'),
			'vsd' => $this->l('Visa Electron'),
			'amx' => $this->l('American Express'),
			'did' => $this->l('Direct Debit (Germany)'),
			'sft' => $this->l('Sofortueberweisung (Germany)')
		);
		
		$result = array();
		
		$i = 0;
		foreach ($variants as $mainKey => $keyData)
		{
			if ( ! array_key_exists($mainKey, $paymentData))
				continue;
			
			foreach ($paymentData[$mainKey] as $option)
			{
				$code = strtolower($paymentData[$keyData['logosKey']][$option]['code']);
				
				$name = false;
				if (array_key_exists($code, $translations))
					$name = $translations[$code];
					
				$smarty->assign('embedScript', $i == 0);

				$smarty->assign(array(
					'payment_name'   => $name,
					'payment_option' => $paymentData[$keyData['logosKey']][$option]['code'],
					'payment_image'  => 'modules/moneybookers/logos/' . $keyData['logosDir'] . '/' . $paymentData[$keyData['logosKey']][$option]['file'] . '.gif'
				));
				
				$result[] = $smarty->fetch(dirname(__FILE__) . '/moneybookers.tpl');
				
				$i++;
			}
		}
		
		return $result;
	}
	
	public function presentPayment($params)
	{
		parent::presentPayment($params);
		$data = $this->collectPaymentData();

		if (isset($data) && sizeof($data))
		{
			if (sizeof($data['paymentParams']))
				$result = $this->presentPaymentOptions($data['paymentParams']);
			
			return sizeof($result) ? $result : false;
		}
		
		return false;
	}
   
	public function callPayment($params)
	{
		global $cookie, $cart, $js_files;
			if ( ! isset($cookie->id_currency))
				$cookie->id_currency = $cart->id_currency;

		// Removing the unnecessary scripts: at this point their presence will
		// only cause errors, since we're already passed the stage of
		// selecting address and payment method, etc.
		$scriptsToRemove = array(
			_MODULE_DIR_ . 'germanext/themes/js/order-opc.js',
			_THEME_JS_DIR_ . 'tools/statesManagement.js'
		);
		
		foreach ($scriptsToRemove as $script)
			if ($key = array_search($script, $js_files))
				unset($js_files[$key]);
				
		$data = $this->collectPaymentData();


		echo '
        <html>
            <head>
                <title>MoneyBookers</title>
                <script type="text/javascript" src="'.$data['url'].'js/jquery/jquery-1.7.2.min.js"></script>
            </head>
            <body>
		<p>'.$data['redirect_text'].'<br /><a href="javascript:history.go(-1);">'.$data['cancel_text'].'</a></p>
		<form method="post" id="paymentForm" action="https://www.moneybookers.com/app/payment.pl">
			<input type="hidden" name="pay_to_email" value="' . $data['orderParams']['pay_to_email'] . '" />
			<input type="hidden" name="recipient_description" value="' . $data['orderParams']['recipient_description'] . '" />
			<input type="hidden" name="transaction_id" value="' . $data['orderParams']['transaction_id'] . '" />
			<input type="hidden" name="return_url" value="' . $data['orderParams']['return_url'] . '" />
			<input type="hidden" name="return_url_text" value="' . $data['orderParams']['return_url'] . '" />
			<input type="hidden" name="cancel_url" value="' . $data['orderParams']['return_url'] . '" />
			<input type="hidden" name="status_url" value="' . $data['orderParams']['status_url'] . '" />
			<input type="hidden" name="status_url2" value="' . $data['orderParams']['pay_to_email'] . '" />
			<input type="hidden" name="language" value="' . $data['orderParams']['language'] . '" />
			<input type="hidden" name="hide_login" value="' . $data['orderParams']['hide_login'] . '" />
			<input type="hidden" name="pay_from_email" value="' . $data['orderParams']['pay_from_email'] . '" />
			<input type="hidden" name="firstname" value="' . $data['orderParams']['firstname'] . '" />
			<input type="hidden" name="lastname" value="' . $data['orderParams']['lastname'] . '" />';
			
			if ( ! empty($data['orderParams']['date_of_birth']))
				echo '<input type="hidden" name="date_of_birth" value="' . $data['orderParams']['date_of_birth'] . '" />';
				
			if ( ! empty($data['orderParams']['address2']))
				echo '<input type="hidden" name="address2" value="' . $data['orderParams']['address2'] . '" />';
				
			if ( ! empty($data['orderParams']['phone_number']))
				echo '<input type="hidden" name="phone_number" value="' . $data['orderParams']['phone_number'] . '" />';
				
			if (isset($data['orderParams']['state']) && !empty($data['orderParams']['state']))
				echo '<input type="hidden" name="state" value="' . $data['orderParams']['state'] . '" />';
			
			echo '
			<input type="hidden" name="address" value="' . $data['orderParams']['address'] . '" />
			<input type="hidden" name="postal_code" value="' . $data['orderParams']['postal_code'] . '" />
			<input type="hidden" name="city" value="' . $data['orderParams']['city'] . '" />
			<input type="hidden" name="country" value="' . $data['orderParams']['country'] . '" />
			<input type="hidden" name="amount" value="' . $data['orderParams']['amount'] . '" />
			<input type="hidden" name="currency" value="' . $data['orderParams']['currency'] . '" />
			<input type="hidden" name="amount2_description" value="' . (isset($data['orderParams']['amount2_description']) ? $data['orderParams']['amount2_description'] : '') . '" />
			<input type="hidden" name="amount2" value="' . (isset($data['orderParams']['amount2']) ? $data['orderParams']['amount2'] : '') . '" />
			<input type="hidden" name="amount3_description" value="' . (isset($data['orderParams']['amount3_description']) ? $data['orderParams']['amount3_description'] : '') . '" />
			<input type="hidden" name="amount3" value="' . (isset($data['orderParams']['amount3']) ? $data['orderParams']['amount3'] : '') . '" />
			<input type="hidden" name="amount4_description" value="' . (isset($data['orderParams']['amount4_description']) ? $data['orderParams']['amount4_description'] : '') . '" />
			<input type="hidden" name="amount4" value="' . (isset($data['orderParams']['amount4']) ? $data['orderParams']['amount4'] : '') . '" />
			<input type="hidden" name="return_url_target" value="2">
			<input type="hidden" name="cancel_url_target" value="2">
			<input type="hidden" name="merchant_fields" value="platform">
			<input type="hidden" name="platform" value="21445510">
			<input type="hidden" name="payment_methods" value="' . $_GET['payment_option'] . '" />';
			
		echo '
		</form>
		<script type="text/javascript">
			$(document).ready(function(){
				$("form#paymentForm").submit();
			});
		</script>	</body>
</html>';
        exit;
	}
}

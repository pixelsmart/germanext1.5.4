<?php
abstract class HTMLTemplate extends HTMLTemplateCore
{
    protected function getTemplate($template_name)
	{
		$template = false;
		$template_file = GN_PDF_PATH.$template_name.'.tpl';

		if (file_exists($template_file))
			$template = $template_file;
            
        if ( ! $template)
        {
            return parent::getTemplate($template_name);
        }

		return $template;
	}
	
	public function getFooter()
	{
		$gn_instance = Module::getInstanceByName('germanext');
		$shop_address = $this->getShopAddress();
		
		$gn_address_rows = array();
		$gn_address_row_names = array(
			'PS_SHOP_COMPANY' => $gn_instance->getPdfTranslation('Company'),
			'PS_SHOP_ADDR1|PS_SHOP_ADDR2' => $gn_instance->getPdfTranslation('Address'),
			'PS_SHOP_CODE' => $gn_instance->getPdfTranslation('Post/Zip code'),
			'PS_SHOP_CITY' => $gn_instance->getPdfTranslation('City'),
			'PS_SHOP_STATE' => $gn_instance->getPdfTranslation('State'),
			'PS_SHOP_COUNTRY' => $gn_instance->getPdfTranslation('Country'),
			'PS_SHOP_PHONE' => $gn_instance->getPdfTranslation('Phone'),
			'PS_SHOP_FAX' => $gn_instance->getPdfTranslation('Fax'),
			'PS_SHOP_EMAIL' => $gn_instance->getPdfTranslation('Email'),
			'PS_SHOP_REPRESENTER' => $gn_instance->getPdfTranslation('Authorised representative'),
			'PS_SHOP_REGISTER_COURT' => $gn_instance->getPdfTranslation('Register court'),
			'PS_SHOP_REGISTER_NUM' => $gn_instance->getPdfTranslation('Register number'),
			'PS_SHOP_SALES_TAX_ID' => $gn_instance->getPdfTranslation('Sales tax ID number'),
			'PS_SHOP_BANK_NAME' => $gn_instance->getPdfTranslation('Bank name'),
			'PS_SHOP_BANK_ACCOUNT' => $gn_instance->getPdfTranslation('Account number'),
			'PS_SHOP_BANK_CODE' => $gn_instance->getPdfTranslation('Bank identifier code'),
			'PS_SHOP_BANK_IBAN' => $gn_instance->getPdfTranslation('IBAN'),
			'PS_SHOP_BANK_SWIFT' => $gn_instance->getPdfTranslation('SWIFT'),
		);
		
		foreach ($gn_address_row_names as $config_var => $name)
		{
			$value = '';
			
			if (strpos($config_var, '|') !== false)
			{
				$config_var = explode('|', $config_var);

				foreach ($config_var as $config)
				{
					$tmp = Configuration::get($config);
					
					if ($tmp && ! Tools::isEmpty($tmp))
					{
						$value.= $tmp . '<br />';
					}
				}
				
				$value = rtrim($value, '<br />');
			}
			else
			{
				$value = Configuration::get($config_var);
			}

			if ( ! Tools::isEmpty($value))
			{
				array_push($gn_address_rows, array(
					'name' => $name,
					'value' => $value
				));
			}
		}
		
		$this->smarty->assign(array(
			'available_in_your_account' => $this->available_in_your_account,
			'shop_address' => $shop_address,
			'shop_fax' => Configuration::get('PS_SHOP_FAX', null, null, (int)$this->order->id_shop),
			'shop_phone' => Configuration::get('PS_SHOP_PHONE', null, null, (int)$this->order->id_shop),
			'shop_details' => Configuration::get('PS_SHOP_DETAILS', null, null, (int)$this->order->id_shop),
			'free_text' => Configuration::get('PS_INVOICE_FREE_TEXT', (int)Context::getContext()->language->id, null, (int)$this->order->id_shop),
			'footer_address_rows' => $gn_address_rows
		));

		return $this->smarty->fetch($this->getTemplate('footer'));
	}
}


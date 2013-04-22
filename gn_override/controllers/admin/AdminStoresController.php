<?php
class AdminStoresController extends AdminStoresControllerCore
{
	protected function _getDefaultFieldsContent()
	{
		$this->context = Context::getContext();
		$countryList = array();
		$countryList[] = array('id' => '0', 'name' => $this->l('Choose your country'));
        
		foreach (Country::getCountries($this->context->language->id) as $country)
        {
			$countryList[] = array('id' => $country['id_country'], 'name' => $country['name']);
        }
        
		$stateList = array();
		$stateList[] = array('id' => '0', 'name' => $this->l('Choose your state (if applicable)'));
        
		foreach (State::getStates($this->context->language->id) as $state)
        {
			$stateList[] = array('id' => $state['id_state'], 'name' => $state['name']);
        }

		$formFields = array(
			'PS_SHOP_NAME' => array(
				'title' => $this->l('Shop name:'),
				'desc' => $this->l('Displayed in emails and page titles'),
				'validation' => 'isGenericName',
				'required' => true,
				'size' => 30,
				'type' => 'text'
			),
			'PS_SHOP_EMAIL' => array(
				'title' => $this->l('Shop email:'),
				'desc' => $this->l('Displayed in emails sent to customers'),
				'validation' => 'isEmail',
				'required' => true,
				'size' => 30,
				'type' => 'text'
			),
			'PS_SHOP_COMPANY' => array(
				'title' => $this->l('Company name:'),
				'desc' => $this->l('Displayed in e-mails and page titles'),
				'validation' => 'isGenericName',
				'required' => false,
				'size' => 30,
				'type' => 'text'
			),
			'PS_SHOP_ADDR1' => array(
				'title' => $this->l('Shop address line 1:'),
				'validation' => 'isAddress',
				'size' => 30,
				'type' => 'text'
			),
			'PS_SHOP_ADDR2' => array(
				'title' => $this->l('Address line 2'),
				'validation' => 'isAddress',
				'size' => 30,
				'type' => 'text'
			),
			'PS_SHOP_CODE' => array(
				'title' => $this->l('Post/Zip code:'),
				'validation' =>
				'isGenericName',
				'size' => 6,
				'type' => 'text'
			),
			'PS_SHOP_CITY' => array(
				'title' => $this->l('City:'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text'
			),
			'PS_SHOP_COUNTRY_ID' => array(
				'title' => $this->l('Country:'),
				'validation' => 'isInt',
				'type' => 'select',
				'list' => $countryList,
				'identifier' => 'id',
				'cast' => 'intval',
				'defaultValue' => (int)$this->context->country->id
			),
			'PS_SHOP_STATE_ID' => array(
				'title' => $this->l('State:'),
				'validation' => 'isInt',
				'type' => 'select',
				'list' => $stateList,
				'identifier' => 'id',
				'cast' => 'intval'
			),
			'PS_SHOP_PHONE' => array(
				'title' => $this->l('Phone:'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text'
			),
			'PS_SHOP_FAX' => array(
				'title' => $this->l('Fax:'),
				'validation' =>
				'isGenericName',
				'size' => 30,
				'type' => 'text'
			),
 			'PS_SHOP_REPRESENTER' => array(
				'title' => $this->l('Authorised representative:'),
				'desc' => $this->l('authorised representative information'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'textarea',
				'cols' => 30,
				'rows' => 2
			),
 			'PS_SHOP_REGISTER_COURT' => array(
				'title' => $this->l('Register court:'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'textarea',
				'cols' => 30,
				'rows' => 2
			),
 			'PS_SHOP_REGISTER_NUM' => array(
				'title' => $this->l('Register number:'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text'
			),
 			'PS_SHOP_SALES_TAX_ID' => array(
				'title' => $this->l('Sales tax ID number:'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text'
			),
 			'PS_SHOP_BANK_NAME' => array(
				'title' => $this->l('Bank name:'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text'
			),
 			'PS_SHOP_BANK_ACCOUNT' => array(
				'title' => $this->l('Account number:'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text'
			),
 			'PS_SHOP_BANK_CODE' => array(
				'title' => $this->l('Bank identifier code:'),
				'desc' => $this->l('Bank national identifier code'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text'
			),
 			'PS_SHOP_BANK_IBAN' => array(
				'title' => $this->l('IBAN:'),
				'desc' => $this->l('International Bank Account Number'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text'
			),
 			'PS_SHOP_BANK_SWIFT' => array(
				'title' => $this->l('SWIFT:'),
				'desc' => $this->l('Society for Worldwide Interbank Financial Telecommunications'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'text'
			),
 			'PS_SHOP_DETAILS' => array(
				'title' => $this->l('Other information'),
				'validation' => 'isGenericName',
				'size' => 30,
				'type' => 'textarea',
				'cols' => 30,
				'rows' => 5
			)
		);
		return $formFields;
	}
}

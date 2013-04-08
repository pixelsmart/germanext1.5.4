<?php

class PaymentCostCore extends ObjectModel
{
	public $id_payment;
	public $module;
	public $cost_name;
   	public $impact_dir;
	public $impact_type;
	public $impact_value;
	public $active;
	
	protected $fieldsRequired = array('id_payment', 'module');
	protected $fieldsSize = array('module' => 100);
	protected $fieldsValidate = array(
		'id_payment' => 'isUnsignedId',
		'module' => 'isAnything', 
		'impact_dir' => 'isUnsignedId',
		'impact_type' => 'isUnsignedId',
		'impact_value' => 'isFloat',
		'active' => 'isUnsignedId');

	protected $table = 'payment_cost';
	protected $identifier = 'id_payment';

	//protected   $fieldsRequiredLang = array('cost_name');
	protected   $fieldsSizeLang = array('cost_name' => 128);
	protected   $fieldsValidateLang = array( 'cost_name' => 'isAnything');
   
	public function getFields()
	{
		parent::validateFields();
		$fields['id_payment'] = (int)($this->id_payment);
		$fields['module'] = pSQL($this->module);
		$fields['impact_dir'] = (int)($this->impact_dir);
		$fields['impact_type'] = (int)($this->impact_type);
		$fields['impact_value'] = (float)($this->impact_value);
		$fields['active'] = (int)($this->active);
		return $fields;
	}
	
	public function add($autodate = true, $nullValues = false)
	{
		return false;
	}
   
	public function getPaymentList($bGetPaymentCost = false)
   	{
		if ($bGetPaymentCost) {
			return Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.$this->table.'`');
		}
      
		$context = Context::getContext();
		
		$groups = array();
		
		$shop_list = Shop::getContextListShopID();
		
		if (isset($context->customer) && $context->customer->isLogged()) {
			$groups = $context->customer->getGroups();
		}
		elseif (isset($context->customer) && $context->customer->isLogged(true)) {
			$groups = array((int)Configuration::get('PS_GUEST_GROUP'));
		}
		else {
			$groups = array((int)Configuration::get('PS_UNIDENTIFIED_GROUP'));
		}
		
		$sql = new DbQuery();
		$sql->select('DISTINCT pm.*');
		$sql->from($this->table, 'pm');
		$sql->leftJoin('module', 'm', 'pm.`module` = m.`name`');
		$sql->innerJoin('hook_module', 'hm', 'hm.`id_module` = m.`id_module`');
		$sql->innerJoin('hook', 'h', 'hm.`id_hook` = h.`id_hook`');
		$sql->where('(SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'module_shop ms WHERE ms.id_module = m.id_module AND ms.id_shop IN ('.implode(', ', $shop_list).')) = '.count($shop_list));
		
		if (Validate::isLoadedObject($context->country))
			$sql->where('(h.name = "displayPayment" AND (SELECT id_country FROM '._DB_PREFIX_.'module_country mc WHERE mc.id_module = m.id_module AND id_country = '.(int)$context->country->id.' LIMIT 1) = '.(int)$context->country->id.')');
		if (Validate::isLoadedObject($context->currency))
			$sql->where('(h.name = "displayPayment" AND (SELECT id_currency FROM '._DB_PREFIX_.'module_currency mcr WHERE mcr.id_module = m.id_module AND id_currency IN ('.(int)$context->currency->id.', -2) LIMIT 1) IN ('.(int)$context->currency->id.', -2))');

		if (Validate::isLoadedObject($context->shop)) {
			$sql->where('hm.id_shop = '.(int)$context->shop->id);
		}
		
		$sql->leftJoin('module_group', 'mg', 'mg.`id_module` = m.`id_module`');
		$sql->where('mg.`id_group` IN ('.implode(', ', $groups).')');
		$sql->orderBy('hm.`position`, m.`name`');
		
      		return Db::getInstance()->ExecuteS($sql);
      	}
   
	public function getPriceImpact($price)
	{
		$impact = (float)$this->impact_value;

		if ($this->impact_type == 0) {
			$impact = ((float)$price) * ($impact/100);
		}
		
		if ($this->impact_dir == 1)  {
			return $impact; 
		}
		
      		if ($this->impact_dir == 2)  {
      			return $impact*(-1);
      		}
      		
		return 0;
   	}
   	
	static public function s_getPriceImpact($id_payment, $price)
	{
		$paymentCost = new PaymentCost($id_payment);
		
		return $paymentCost->getPriceImpact($price);
	}
   
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang(true, false);
		$fields = array();
		$languages = Language::getLanguages(false);
		
		foreach ($languages as $language) {
			$fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
			$fields[$language['id_lang']][$this->identifier] = (int)($this->id);
			$fields[$language['id_lang']]['cost_name'] = (isset($this->cost_name[$language['id_lang']])) ? pSQL($this->cost_name[$language['id_lang']], true) : '';
		}
		
		return $fields;
	}
   
	static public function getFeeTitle($paymentModuleId, $languageId = null)
	{
		if ( ! Validate::isUnsignedId($paymentModuleId) || isset($languageId) && ! Validate::isUnsignedId($languageId)) {
			die(Tools::displayError());
		}
			
		$languageId = isset($languageId) ? (int)$languageId : (int)Configuration::get('PS_LANG_DEFAULT');
			
		$paymentCost = new PaymentCost((int)$paymentModuleId, $languageId);
		
		if (Validate::isLoadedObject($paymentCost)) {
			return $paymentCost->cost_name;
		}
		
		return ;
	}
	
	public static function getPaymentIdByModuleId($module_id)
	{
		$payment_id = 0;
		
		$module_name = Db::getInstance()->getValue('
			SELECT
				`name`
			FROM
				`' . _DB_PREFIX_ . 'module`
			WHERE
				`id_module` = ' . (int)$module_id
		);

		if ($module_name && ! Tools::isEmpty($module_name))
		{
			$payment_id = Db::getInstance()->getValue('
				SELECT
					`id_payment`
				FROM
					`' . _DB_PREFIX_ . 'payment_cost`
				WHERE
					`module` = "' . pSQL($module_name) . '"'
			);
		}
		
		return (int)$payment_id > 0 ? (int)$payment_id : false;
	}

	public static function getModuleIdByPaymentId($id_payment)
	{
		$module_id = 0;
		
		$module_name = Db::getInstance()->getValue('
			SELECT
				`module`
			FROM
				`' . _DB_PREFIX_ . 'payment_cost`
			WHERE
				`id_payment` = "' . (int)($id_payment) . '"'
		);
		if ($module_name && ! Tools::isEmpty($module_name))
		{
			$module_id = Db::getInstance()->getValue('
				SELECT
					`id_module`
				FROM
					`' . _DB_PREFIX_ . 'module`
				WHERE
					`name` = "' . pSQL($module_name) . '"'
			);
		}
		
		return (int)$module_id > 0 ? (int)$module_id : false;
	}
}

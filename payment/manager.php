<?php
/**
 * germanext Module
 *
 * @version 1.4.1
 *
 * @category  German Extension
 * @author    Sergej Tiessen <sergej@silbersaiten.de>
 * @copyright Silbersaiten GbR <www.silbersaiten.de>
 *
 * Support: http://www.silbersaiten.de/
 *
 */

abstract class GN_PaymentManager
{
	public $id;
	public $_dir;
	public $_path;
	public $_relPath;
	public $_publicName;
	public $_publicDescription;
	public $_defaultTpl = true;
   
	protected static $_modules = array();
	protected $_gnInstance = false;
   
	public function __construct() 
	{
		$this->_path    = _PS_MODULE_DIR_ . 'germanext/payment/modules/' . $this->_dir . '/';
		$this->_relPath = _MODULE_DIR_ . 'germanext/payment/modules/' . $this->_dir . '/';
        
		// We'll need a module's instance for translation purposes
		if ( ! $this->_gnInstance) {
			$this->_gnInstance = Module::getInstanceByName('germanext');
		}
	}
   
	public function presentPayment($params)
	{
		$context = Context::getContext();
        
		$this->_publicName = $this->_publicName ? $this->_publicName : $this->_dir;
        
		$context->smarty->assign(array(
			'paymentTitle'       => $this->_publicName,
			'paymentDescription' => $this->_publicDescription,
			'paymentLogo'        => $this->getPaymentLogo()
		));
        
		if ($this->_defaultTpl) {
			return $context->smarty->fetch(dirname(__FILE__) . '/default.tpl');
		}
	}
   
	public function callPayment($params){}
   
	public function getPaymentLogo()
	{
		if ( ! file_exists($this->_path . 'logo.jpg')) {
			return false;
		}
       
		return $this->_relPath . 'logo.jpg';
	}
   
	public function l($string, $specific = false)
	{
		return $this->_gnInstance->l($string, $specific ? $specific : $this->_dir);
	}

	public static function getContentVars()
	{
		$context = Context::getContext();
        
		$context->smarty->assign(array(
			'GN_CHECK_PAYMENT' => Configuration::get('GN_CHECK_PAYMENT'),
			'GN_PAYMENT_LIST'  => self::getPaymentList(),  
			'GN_LANGUAGES'     => Language::getLanguages(false),
			'GN_LANG_DEFAULT'  => Configuration::get('PS_LANG_DEFAULT')
		));
	}
   
	public static function getPaymentList()
	{
		$paymentCost = new PaymentCost();
		$paymentList = $paymentCost->getPaymentList(TRUE);
		$languages = Language::getLanguages(false);
		$List = array();
       
		if ( ! is_array($paymentList) || ! sizeof($paymentList)) {
			return false;
		}
       
		foreach ($paymentList as $payment) {
			$paymentCost = new PaymentCost($payment['id_payment']);
			$payment_n = $payment;
			$payment_n['cost_name'] = array();
            
			foreach ($languages as $language) {
				$id_lang = $language['id_lang'];
				$str = (isset($paymentCost->cost_name[$id_lang])) ?  $paymentCost->cost_name[$id_lang] : '';
				$payment_n['cost_name'][] = array('id_lang'=>$id_lang, 'string'=> $str);
			}
            
			$List[] = $payment_n;
		}
       
		return $List;
	}
    
	public static function postProcess($new_module_id = 0)
	{
		$check_payment = (Tools::getValue('GN_CHECK_PAYMENT', 0)) ? 1 : 0; 
		Configuration::updateValue('GN_CHECK_PAYMENT', $check_payment);
       
		$paymentCost = new PaymentCost();
		$paymentList = $paymentCost->getPaymentList(true);
		$languages = Language::getLanguages(false);
        
		if (is_array($paymentList) && sizeof($paymentList)) {
			foreach ($paymentList as $payment) {
				if ($payment['id_payment'] != $new_module_id) {
					$paymentCost = new PaymentCost($payment['id_payment']);
					$paymentCost->impact_dir = (int) Tools::getValue('GN_PAYMENT_IMPACT_'.$payment['id_payment'], 0); 
					$paymentCost->impact_type   = (int) Tools::getValue('GN_PAYMENT_TYPE_'.$payment['id_payment'], 0);         
					$paymentCost->impact_value  = (float) Tools::getValue('GN_PAYMENT_VALUE_'.$payment['id_payment'], 0);
                    
					foreach ($languages as $language) {
						$paymentCost->cost_name[$language['id_lang']] = Tools::getValue('GN_PAYMENT_COST_NAME_'.$payment['id_payment'].'_'.$language['id_lang'], '');
					}
                    
					if ($paymentCost->impact_value < 0) {
						$paymentCost->impact_value = 0;
					}
                    
					$paymentCost->update();
				}
			}
		}
	}
   
	public static function getPaymentInstance($id_payment)
	{
		$paymentCost = new PaymentCost();
		$paymentList = self::getPaymentList(TRUE);
		$module = '';
        
		foreach ($paymentList as $payment) {
			if ($payment['id_payment'] == $id_payment) {
				$module = $payment['module'];
				break;
			}
		}
        
		$path = dirname(__FILE__) . '/modules/' . $module . '/' . $module . '.php';
        
		if ($module == '' || ! file_exists($path)) {
			return null; 
		}
        
		require_once($path);
        
		$module = 'gn_' . $module;
     
		if ( ! isset(self::$_modules[$module])) {
			self::$_modules[$module] = new $module;
		}
           
		return self::$_modules[$module];
	}
}

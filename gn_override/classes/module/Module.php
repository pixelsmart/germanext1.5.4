<?php
abstract class Module extends ModuleCore
{
    public static function getGnTemplatePath($module, $template) {
		if (Module::isInstalled('germanext')) {
			require_once(_PS_MODULE_DIR_ . 'germanext/defines.php');
            
			$tplPath = GN_THEME_PATH . 'modules/' . $module . '/' . $template;
            
			if (file_exists($tplPath)) {
				return $tplPath;
			}
		}
        
		return false;
	}
    
    public function getTemplatePath($template) {
		$overloaded = $this->_isTemplateOverloaded($template);
        
		if (is_null($overloaded)) {
			return null;
		}
        
		if ($gnTpl = self::getGnTemplatePath($this->name, $template)) {
			return $gnTpl;
		}
        
		if ($overloaded) {
			return _PS_THEME_DIR_.'modules/' . $this->name . '/' . $template;
		}
		else if (file_exists(_PS_MODULE_DIR_ . $this->name . '/views/templates/hook/' . $template)) {
			return _PS_MODULE_DIR_ . $this->name . '/views/templates/hook/' . $template;
		}
		else {
			return _PS_MODULE_DIR_ . $this->name . '/' . $template;
		}
	}
}

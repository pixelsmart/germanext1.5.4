<?php
class AdminController extends AdminControllerCore
{
	public function setMedia() {
		parent::setMedia();

		$gn_configs = Configuration::getMultiple(array(
			'GN_FORCE_STAT_GATHER',
			'PS_PSTATISTIC',
			'PS_ORDER_PROCESS_TYPE',
			'PS_CONDITIONS',
			'PS_CMS_ID_CONDITIONS',
			'PS_CMS_ID_REVOCATION',
			'PS_PRIVACY',
			'PS_CMS_ID_PRIVACY',
			'PS_CMS_ID_DELIVERY',
			'PS_CMS_ID_IMPRINT'
		));

		$gn_configs['USTG'] = (Module::isInstalled('smallscaleenterprise') && (int)Configuration::get('USTG_ACTIVE') == 1);

		$this->context->smarty->assign($gn_configs);
	}
	
	public function setGnOverride($template, $overrideObj = false, $up_levels = 5) {
		$overrideObj = $overrideObj ? $overrideObj : $this;
        
		if (Module::isInstalled('germanext')) {
			require_once(_PS_MODULE_DIR_ . 'germanext/germanext.php');
            
			$gn_template = Germanext::getTemplateByController(get_class($this), $template, $this->bo_theme, true);
            
			if ($gn_template !== false) {
				$overrideObj->override_folder = str_repeat('../', $up_levels) . ltrim($gn_template, '/');
                
				return true;
			}
		}
        
		return false;
	}
    
	public function setHelperDisplay(Helper $helper) {
		parent::setHelperDisplay($helper);
        
		if ($this->setGnOverride($this->display . '.tpl', $helper) !== false) {
			// Avoid appending junk folders, like "helpers/view/" etc.
			$helper->base_folder = '';
		}
	}
    
	public function createTemplate($tpl_name) {
		if ($this->setGnOverride($tpl_name) !== false
		&& $this->viewAccess()
		&& $this->override_folder
		&& file_exists($this->context->smarty->getTemplateDir(0).'controllers/'.$this->override_folder.$tpl_name)) {
			return $this->context->smarty->createTemplate($this->context->smarty->getTemplateDir(0).'controllers/'.$this->override_folder.$tpl_name, $this->context->smarty);
		}
        
		return parent::createTemplate($tpl_name);
	}
}

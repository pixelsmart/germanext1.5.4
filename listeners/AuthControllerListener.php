<?php
class AuthControllerListener extends Germanext
{
	public function __construct(){}
	
	public function execute($context)
	{
		$regText = Configuration::get('GN_REGISTRATION_TEXT', (int)$context->cookie->id_lang);
		$regText = ($regText && ! Tools::isEmpty(trim($regText))) ? trim($regText) : false;
		
		$context->smarty->assign(array(
			'GN_REG_TEXT'          => $regText,
			'GN_NEWSLETTER'        => self::getGernamextNewsLetterState(),
			'GN_FORCE_STAT_GATHER' => Configuration::get('GN_FORCE_STAT_GATHER'),
			'PS_PSTATISTIC'        => Configuration::get('PS_PSTATISTIC'),
			'PS_PRIVACY'           => Configuration::get('PS_PRIVACY'),
			'CMS_PRIVACY_LINK'     => self::getCmsLink((int)Configuration::get('PS_CMS_ID_PRIVACY'), (int)$context->cookie->id_lang)
		));
		
		$context->controller->addJs($this->_path . 'js/authentication.js');
	}
}

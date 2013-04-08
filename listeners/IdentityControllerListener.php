<?php
class IdentityControllerListener extends Germanext
{
	public function __construct(){}
	
	public function execute($context)
	{
		$context->smarty->assign(array(
			'GN_NEWSLETTER'        => self::getGernamextNewsLetterState(),
			'GN_FORCE_STAT_GATHER' => Configuration::get('GN_FORCE_STAT_GATHER'),
			'PS_PSTATISTIC'        => Configuration::get('PS_PSTATISTIC')
		));
	}
}

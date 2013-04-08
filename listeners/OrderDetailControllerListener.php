<?php
class OrderDetailControllerListener extends Germanext
{
	public function __construct(){}
	
	public function execute($context)
	{
		$context->smarty->assign('GN_ALLOW_REORDER', Configuration::get('GN_ALLOW_REORDER'));
	}
}

<?php
class ProductControllerListener extends Germanext
{
	public function __construct(){}
	
	public function execute($context)
	{
		$id_lang = (int)$context->cookie->id_lang;
		
		$CMS_SHIPPING_LINK = self::getCmsLink((int)Configuration::get('PS_CMS_ID_DELIVERY'), $id_lang);
		
		$context->smarty->assign('CMS_SHIPPING_LINK', $CMS_SHIPPING_LINK);
	}
}

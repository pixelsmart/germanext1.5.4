<?php
class OrderOpcControllerListener extends Germanext
{
	public function __construct(){}
	
	public function execute($context)
	{
		$id_lang             = (int)$context->cookie->id_lang;
		$CMS_CONDITIONS_LINK = self::getCmsLink((int)Configuration::get('PS_CMS_ID_CONDITIONS'), $id_lang);
		$CMS_REVOCATION_LINK = self::getCmsLink((int)Configuration::get('PS_CMS_ID_REVOCATION'), $id_lang);
		$CMS_PRIVACY_LINK    = self::getCmsLink((int)Configuration::get('PS_CMS_ID_PRIVACY'), $id_lang);
		$CMS_SHIPPING_LINK   = self::getCmsLink((int)Configuration::get('PS_CMS_ID_DELIVERY'), $id_lang);
		$regText             = Configuration::get('GN_REGISTRATION_TEXT', $id_lang);
		$regText             = ($regText && ! Tools::isEmpty(trim($regText))) ? trim($regText) : false;

		$context->smarty->assign(
			array(
				'GN_REG_TEXT'          => $regText,
				'ONLY_SHIPPING_CART'   => Tools::getValue('shipping_cart'),
				'PS_CMS_ID_CONDITIONS' => (int)(Configuration::get('PS_CMS_ID_CONDITIONS')),
				'PS_CMS_ID_REVOCATION' => (int)(Configuration::get('PS_CMS_ID_REVOCATION')),
				'PS_CONDITIONS'        => (int)(Configuration::get('PS_CONDITIONS')),
				'PS_PRIVACY'           => (int)(Configuration::get('PS_PRIVACY')),
				'GN_CHECK_PAYMENT'     => (int)(Configuration::get('GN_CHECK_PAYMENT')),
				'CMS_CONDITIONS_LINK'  => $CMS_CONDITIONS_LINK,
				'CMS_REVOCATION_LINK'  => $CMS_REVOCATION_LINK,
				'CMS_PRIVACY_LINK'     => $CMS_PRIVACY_LINK,
				'CMS_SHIPPING_LINK'    => $CMS_SHIPPING_LINK
			)
		);

		if (Configuration::get('GN_CHECK_PAYMENT')) {
			$this->removeJs($context, 'order-opc.js');
			$context->controller->addJs($this->_path . 'js/order-opc.js');
		}

		$context->controller->addJs($this->_path . 'js/cart-summary.js');
		$context->controller->addJs($this->_path . 'js/authentication.js');

		require_once(GN_PAYMENT_PATH . 'manager.php');
		
		return '
		<script type="text/javascript">
			var GN_CHECK_PAYMENT = ' . (int)Configuration::get('GN_CHECK_PAYMENT') . ';
		</script>';
    }
}

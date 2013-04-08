<?php

class ParentOrderController extends ParentOrderControllerCore
{
	public static $is_mobile = false;
	
	public function init()
	{
		self::$is_mobile = $this->context->getMobileDevice();
		
		if (self::$is_mobile) {
			return parent::init();
		}
		
		FrontController::init();

		/* Disable some cache related bugs on the cart/order */
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

		$this->nbProducts = $this->context->cart->nbProducts();

		global $isVirtualCart;

		// Redirect to the good order process
		if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 0 && Dispatcher::getInstance()->getController() != 'order')
			Tools::redirect('index.php?controller=order');
		if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 && Dispatcher::getInstance()->getController() != 'orderopc')
		{
			if (isset($_GET['step']) && $_GET['step'] == 3)
				Tools::redirect('index.php?controller=order-opc&isPaymentStep=true');
			Tools::redirect('index.php?controller=order-opc');
		}

		if (Configuration::get('PS_CATALOG_MODE'))
			$this->errors[] = Tools::displayError('This store has not accepted your new order.');

		if (Tools::isSubmit('submitReorder') && $id_order = (int)Tools::getValue('id_order'))
		{
			$oldCart = new Cart(Order::getCartIdStatic($id_order, $this->context->customer->id));
			$duplication = $oldCart->duplicate();
			if (!$duplication || !Validate::isLoadedObject($duplication['cart']))
				$this->errors[] = Tools::displayError('Sorry, we cannot renew your order.');
			else if (!$duplication['success'])
				$this->errors[] = Tools::displayError('Some items are not available, we are unable to renew your order');
			else
			{
				$this->context->cookie->id_cart = $duplication['cart']->id;
				$this->context->cookie->write();
				if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1)
					Tools::redirect('index.php?controller=order-opc');
				Tools::redirect('index.php?controller=order');
			}
		}

		if ($this->nbProducts)
		{
			if (CartRule::isFeatureActive())
			{
				if (Tools::isSubmit('submitAddDiscount'))
				{
					if (!($code = trim(Tools::getValue('discount_name'))))
						$this->errors[] = Tools::displayError('You must enter a voucher code');
					elseif (!Validate::isCleanHtml($code))
						$this->errors[] = Tools::displayError('Voucher code invalid');
					else
					{
						if (($cartRule = new CartRule(CartRule::getIdByCode($code))) && Validate::isLoadedObject($cartRule))
						{
							if ($error = $cartRule->checkValidity($this->context, false, true))
								$this->errors[] = $error;
							else
							{
								$this->context->cart->addCartRule($cartRule->id);
								Tools::redirect('index.php?controller=order-opc&shipping_cart=1');
							}
						}
						else
							$this->errors[] = Tools::displayError('This voucher does not exists');
					}
					$this->context->smarty->assign(array(
						'errors' => $this->errors,
						'discount_name' => Tools::safeOutput($code)
					));
				}
				elseif (($id_cart_rule = (int)Tools::getValue('deleteDiscount')) && Validate::isUnsignedId($id_cart_rule))
				{
					$this->context->cart->removeCartRule($id_cart_rule);
					Tools::redirect('index.php?controller=order-opc');
				}
			}
			/* Is there only virtual product in cart */
			if ($isVirtualCart = $this->context->cart->isVirtualCart())
				$this->setNoCarrier();
		}

		$this->context->smarty->assign('back', Tools::safeOutput(Tools::getValue('back')));
	}
}


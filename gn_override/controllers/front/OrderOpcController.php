<?php
class OrderOpcController extends OrderOpcControllerCore
{
	public  static $is_mobile = false;

	public function init() {
		self::$is_mobile = $this->context->getMobileDevice();
		
		if (self::$is_mobile) {
			return parent::init();
		}
		
		ParentOrderController::init();

		if ($this->nbProducts) {
			$this->context->smarty->assign('virtual_cart', false);
        }
		
		$this->context->smarty->assign('is_multi_address_delivery', $this->context->cart->isMultiAddressDelivery() || ((int)Tools::getValue('multi-shipping') == 1));
		$this->context->smarty->assign('open_multishipping_fancybox', (int)Tools::getValue('multi-shipping') == 1);
		
		$this->isLogged = (bool)($this->context->customer->id && Customer::customerIdExistsStatic((int)$this->context->cookie->id_customer));

		if ($this->context->cart->nbProducts()) {
            if (Tools::isSubmit('callOrder') && Tools::isSubmit('payment_module')) {
                require_once(_PS_MODULE_DIR_ . 'germanext/payment/manager.php');
                
				$id_module = Tools::getValue('payment_module');
				
				$instance = GN_PaymentManager::getPaymentInstance($id_module);

				$instance->callPayment($this->context);
			}
            
			if (Tools::isSubmit('ajax')) {
				if (Tools::isSubmit('method')) {
					switch (Tools::getValue('method')) {
						case 'updatePaymentModule':
							if (Tools::isSubmit('payment_module')) {
								$paymentCost = new PaymentCost();
								$paymentList = $paymentCost->getPaymentList();
								$id_module   = (int)Tools::getValue('payment_module');
                        
								$this->context->cart->id_payment = $id_module;
								$this->context->cart->update();
                        
								$order_href = $this->getOrderHref($id_module);
                        
								$return = array(
								   'summary'           => $this->context->cart->getSummaryDetails(), 
								   'HOOK_TOP_PAYMENT'  => Hook::exec('paymentTop'),
								   'HOOK_PAYMENT'      => self::presentPaymentHook(),
								   'BUTTON_ORDER_HREF' => $order_href
								);

								if (sizeof($paymentList)) {
									foreach ($paymentList as $gnModuleData) {
										if ($gnModuleData['id_payment'] == $id_module) {
											$module = GN_PaymentManager::getPaymentInstance($id_module);
											
											if (isset($module) && method_exists($module, 'ajaxDataPrompt')) {
												$return['dataPrompt'] =
												'<form method="post" class="std" action="' . $order_href . '" id="submitPayment_' . $id_module . '">
													<fieldset>
													' . $module->ajaxDataPrompt($this->context) . '
													</fieldset>
												</form>';
											}
											
											break;
										}
                                    }
                                }
								
								die( Tools::jsonEncode($return) );
							}
							break;
                        
						case 'updateMessage':
							if (Tools::isSubmit('message')) {
								$txtMessage = urldecode(Tools::getValue('message'));
								$this->_updateMessage($txtMessage);
								if (count($this->errors))
									die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
								die(true);
							}
							break;

						case 'updateCarrierAndGetPayments':
							if ((Tools::isSubmit('delivery_option') || Tools::isSubmit('id_carrier')) && Tools::isSubmit('recyclable') && Tools::isSubmit('gift') && Tools::isSubmit('gift_message')) {
								$this->_assignWrappingAndTOS();
								if ($this->_processCarrier()) {
									$carriers = $this->context->cart->simulateCarriersOutput();
									$return = array(
										'summary' => $this->context->cart->getSummaryDetails(),
										'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
										'HOOK_PAYMENT' => $this->presentPaymentHook(),
										'carrier_data' => $this->_getCarrierList(),
										'HOOK_BEFORECARRIER' => Hook::exec('displayBeforeCarrier', array('carriers' => $carriers))
									);
									Cart::addExtraCarriers($return);
									die(Tools::jsonEncode($return));
								}
								else {
									$this->errors[] = Tools::displayError('Error occurred while updating cart.');
								}

								if (count($this->errors)) {
									die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
								}

								exit;
							}
							break;

						case 'updateTOSStatusAndGetPayments':
							if (Tools::isSubmit('checked')) {
								$this->context->cookie->checkedTOS = (int)(Tools::getValue('checked'));
								die(Tools::jsonEncode(array(
									'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
									'HOOK_PAYMENT' => $this->presentPaymentHook()
								)));
							}
							break;

						case 'getCarrierList':
							die(Tools::jsonEncode($this->_getCarrierList()));
							break;

						case 'editCustomer':
							if (!$this->isLogged) {
								exit;
							}

							if (Tools::getValue('years')) {
								$this->context->customer->birthday = (int)Tools::getValue('years').'-'.(int)Tools::getValue('months').'-'.(int)Tools::getValue('days');
							}
							
							$_POST['lastname'] = $_POST['customer_lastname'];
							$_POST['firstname'] = $_POST['customer_firstname'];
							$this->errors = $this->context->customer->validateController();
							$this->context->customer->newsletter = (int)Tools::isSubmit('newsletter');
							$this->context->customer->optin = (int)Tools::isSubmit('optin');
							$return = array(
								'hasError' => !empty($this->errors),
								'errors' => $this->errors,
								'id_customer' => (int)$this->context->customer->id,
								'token' => Tools::getToken(false)
							);

							if (!count($this->errors)) {
								$return['isSaved'] = (bool)$this->context->customer->update();
							}
							else {
								$return['isSaved'] = false;
							}

							die(Tools::jsonEncode($return));
							break;

						case 'getAddressBlockAndCarriersAndPayments':
							if ($this->context->customer->isLogged()) {
								// check if customer have addresses
								if (!Customer::getAddressesTotalById($this->context->customer->id)) {
									die(Tools::jsonEncode(array('no_address' => 1)));
								}

								if (file_exists(_PS_MODULE_DIR_.'blockuserinfo/blockuserinfo.php')) {
									include_once(_PS_MODULE_DIR_.'blockuserinfo/blockuserinfo.php');
									$blockUserInfo = new BlockUserInfo();
								}

								$this->context->smarty->assign('isVirtualCart', $this->context->cart->isVirtualCart());
								$this->_processAddressFormat();
								$this->_assignAddress();
								// Wrapping fees
								$wrapping_fees = $this->context->cart->getGiftWrappingPrice(false);
								$wrapping_fees_tax_inc = $wrapping_fees = $this->context->cart->getGiftWrappingPrice();
								$return = array(
									'summary' => $this->context->cart->getSummaryDetails(),
									'order_opc_adress' => $this->context->smarty->fetch(_PS_THEME_DIR_.'order-address.tpl'),
									'block_user_info' => (isset($blockUserInfo) ? $blockUserInfo->hookTop(array()) : ''),
									'carrier_data' => $this->_getCarrierList(),
									'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
									'HOOK_PAYMENT' => $this->presentPaymentHook(),
									'no_address' => 0,
									'gift_price' => Tools::displayPrice(Tools::convertPrice(Product::getTaxCalculationMethod() == 1 ? $wrapping_fees : $wrapping_fees_tax_inc, new Currency((int)($this->context->cookie->id_currency))))
								);
								die(Tools::jsonEncode($return));
							}
							die(Tools::displayError());
							break;

						case 'makeFreeOrder':
							/* Bypass payment step if total is 0 */
							if (($id_order = $this->_checkFreeOrder()) && $id_order) {
								$email = $this->context->customer->email;
								if ($this->context->customer->is_guest)
									$this->context->customer->logout(); // If guest we clear the cookie for security reason
								die('freeorder:'.$id_order.':'.$email);
							}
							exit;
							break;

						case 'updateAddressesSelected':
							if ($this->context->customer->isLogged(true)) {
								$address_delivery = new Address((int)(Tools::getValue('id_address_delivery')));
								$address_invoice = ((int)(Tools::getValue('id_address_delivery')) == (int)(Tools::getValue('id_address_invoice')) ? $address_delivery : new Address((int)(Tools::getValue('id_address_invoice'))));
								if ($address_delivery->id_customer != $this->context->customer->id || $address_invoice->id_customer != $this->context->customer->id) {
									$this->errors[] = Tools::displayError('This address is not yours.');
								}
								elseif (!Address::isCountryActiveById((int)(Tools::getValue('id_address_delivery')))) {
									$this->errors[] = Tools::displayError('This address is not in a valid area.');
								}
								elseif (!Validate::isLoadedObject($address_delivery) || !Validate::isLoadedObject($address_invoice) || $address_invoice->deleted || $address_delivery->deleted) {
									$this->errors[] = Tools::displayError('This address is invalid.');
								}
								else {
									$this->context->cart->id_address_delivery = (int)(Tools::getValue('id_address_delivery'));
									$this->context->cart->id_address_invoice = Tools::isSubmit('same') ? $this->context->cart->id_address_delivery : (int)(Tools::getValue('id_address_invoice'));
									
									if ( ! $this->context->cart->update()) {
										$this->errors[] = Tools::displayError('An error occurred while updating your cart.');
									}

									if ( ! $this->context->cart->isMultiAddressDelivery()) {
										$this->context->cart->setNoMultishipping(); // As the cart is no multishipping, set each delivery address lines with the main delivery address
									}

									if ( ! count($this->errors)) {
										$result = $this->_getCarrierList();
										// Wrapping fees
										$wrapping_fees = $this->context->cart->getGiftWrappingPrice(false);
										$wrapping_fees_tax_inc = $wrapping_fees = $this->context->cart->getGiftWrappingPrice();
										$result = array_merge($result, array(
											'summary' => $this->context->cart->getSummaryDetails(),
											'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
											'HOOK_PAYMENT' => $this->presentPaymentHook(),
											'gift_price' => Tools::displayPrice(Tools::convertPrice(Product::getTaxCalculationMethod() == 1 ? $wrapping_fees : $wrapping_fees_tax_inc, new Currency((int)($this->context->cookie->id_currency)))),
											'carrier_data' => $this->_getCarrierList()
										));
										die(Tools::jsonEncode($result));
									}
								}

								if (count($this->errors)) {
									die('{"hasError" : true, "errors" : ["'.implode('\',\'', $this->errors).'"]}');
								}
							}
							die(Tools::displayError());
							break;

						case 'multishipping':
							$this->_assignSummaryInformations();
							if ($this->context->customer->id) {
								$this->context->smarty->assign('address_list', $this->context->customer->getAddresses($this->context->language->id));
							}
							else {
								$this->context->smarty->assign('address_list', array());
							}

							$this->setTemplate(_PS_THEME_DIR_.'order-address-multishipping-products.tpl');
							$this->display();
							die();
							break;

						case 'cartReload':
							$this->_assignSummaryInformations();
							if ($this->context->customer->id) {
								$this->context->smarty->assign('address_list', $this->context->customer->getAddresses($this->context->language->id));
							}
							else {
								$this->context->smarty->assign('address_list', array());
							}

							$this->context->smarty->assign('opc', true);
							$this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
							$this->display();
							die();
							break;

						case 'noMultiAddressDelivery':
							$this->context->cart->setNoMultishipping();
							die();
							break;

						default:
							throw new PrestaShopException('Unknown method "'.Tools::getValue('method').'"');
					}
				}
				else {
					throw new PrestaShopException('Method is not defined');
				}
			}
		}
		elseif (Tools::isSubmit('ajax')) {
			throw new PrestaShopException('Method is not defined');
		}
	}
    
    public function getOrderHref($id_module = false) {
        $id_module = $id_module ? $id_module : $this->context->cart->id_payment;
        
        if ((int)$id_module != 0) {
            return $this->context->link->getPageLink('order-opc.php', true, null, array('callOrder' => true, 'payment_module' => $id_module));
        }
        
        return ;
    }
    
    protected function _checkPaymentError($check_conditions) {
		if ( ! $this->isLogged) {
			return '<p class="warning">'.Tools::displayError('Please sign in to see payment methods.').'</p>';
        }
        
		if ($this->context->cart->OrderExists()) {
			return '<p class="warning">'.Tools::displayError('Error: This order has already been validated.').'</p>';
        }
        
		if (!$this->context->cart->id_customer
            || !Customer::customerIdExistsStatic($this->context->cart->id_customer)
            || Customer::isBanned($this->context->cart->id_customer)) {
			return '<p class="warning">'.Tools::displayError('Error: No customer.').'</p>';
        }
        
		$address_delivery = new Address($this->context->cart->id_address_delivery);
		$address_invoice = ($this->context->cart->id_address_delivery == $this->context->cart->id_address_invoice ? $address_delivery : new Address($this->context->cart->id_address_invoice));
		if (!$this->context->cart->id_address_delivery
            || !$this->context->cart->id_address_invoice
            || !Validate::isLoadedObject($address_delivery)
            || !Validate::isLoadedObject($address_invoice)
            || $address_invoice->deleted
            || $address_delivery->deleted) {
			return '<p class="warning">'.Tools::displayError('Error: Please choose an address.').'</p>';
        }
        
		if (count($this->context->cart->getDeliveryOptionList()) == 0) {
			if ($this->context->cart->isMultiAddressDelivery()) {
				return '<p class="warning">'.Tools::displayError('Error: None of your chosen carriers deliver to some of  the addresses you\'ve selected.').'</p>';
            } 
			else {
				return '<p class="warning">'.Tools::displayError('Error: None of your chosen carriers deliver to the address you\'ve selected.').'</p>';
            }
		}
        
		if (!$this->context->cart->getDeliveryOption(null, false) && !$this->context->cart->isVirtualCart()) {
			return '<p class="warning">'.Tools::displayError('Error: Please choose a carrier.').'</p>';
        }
        
		if (!$this->context->cart->id_currency) {
			return '<p class="warning">'.Tools::displayError('Error: No currency has been selected.').'</p>';
        }
        
		if ($check_conditions && ! $this->context->cookie->checkedTOS && Configuration::get('PS_CONDITIONS')) {
			return '<p class="warning">'.Tools::displayError('Please accept the Terms of Service.').'</p>';
        }

		/* If some products have disappear */
		if (!$this->context->cart->checkQuantities()) {
			return '<p class="warning">'.Tools::displayError('An item in your cart is no longer available. You cannot proceed with your order.').'</p>';
        }

		/* Check minimal amount */
		$currency = Currency::getCurrency((int)$this->context->cart->id_currency);

		$minimalPurchase = Tools::convertPrice((float)Configuration::get('PS_PURCHASE_MINIMUM'), $currency);
		if ($this->context->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) < $minimalPurchase) {
 			return '<p class="warning">'.sprintf(
				Tools::displayError('A minimum purchase total of %s is required in order to validate your order.'),
 				Tools::displayPrice($minimalPurchase, $currency)
 			).'</p>';
        }

		return 0;
    }
    
    protected function _getPaymentMethods() {
        $error = $this->_checkPaymentError(true);
        
        if ($error) {
            return $error;
        }
        
        $return = Hook::exec('displayPayment');
        
		if (!$return) {
			return '<p class="warning">'.Tools::displayError('No payment method is available for use at this time. ').'</p>';
        }
		return $return;
    }
    
	protected function _assignPayment() {
		$this->context->smarty->assign(array(
			'HOOK_TOP_PAYMENT' => ($this->isLogged ? Hook::exec('displayPaymentTop') : ''),
			'HOOK_PAYMENT' => $this->presentPaymentHook(),
            'BUTTON_ORDER_HREF' => $this->getOrderHref()
		));
	}
    
	private function presentPaymentHook() {
		if ((int)Configuration::get('GN_CHECK_PAYMENT') != 1 || self::$is_mobile) {
			return parent::_getPaymentMethods();
        }
        
        require_once(_PS_MODULE_DIR_ . 'germanext/payment/manager.php');

        $error = $this->_checkPaymentError(false);
		
		if ($error) {
			return $error;
        }

		$paymentCost = new PaymentCost();
		$paymentList = $paymentCost->getPaymentList();
      
		$modules = array();
		
		foreach ($paymentList as $payment) {
			$id_payment = (int)$payment['id_payment'];
			
			$instance = GN_PaymentManager::getPaymentInstance($id_payment);
		   
			$result = $instance->presentPayment($id_payment);
		   
			if ($result !== false) {
				if ( ! is_array($result)) {
					$result = array($result);
                }
					
				foreach ($result as $method) {
					$modules[] = array('id' => $id_payment, 'content' => $method);
                }
			}
		}
		
		$HOOK_PAYMENT = '';
		
		if (sizeof($modules) > 0) {
			$this->context->smarty->assign(array('PAYMENT_METHOD_LIST_ONLY'=> $modules, 'current_id_payment' => $this->context->cart->id_payment));
			$GN = new Germanext();
			$HOOK_PAYMENT = $this->context->smarty->fetch(GN_THEME_PATH . 'order-payment.tpl');
			$this->context->smarty->assign(array('PAYMENT_METHOD_LIST_ONLY'=> 0));
		}
		
		return $HOOK_PAYMENT;
    }
	
	protected function _getCarrierList() {
		$result = parent::_getCarrierList();
	
		if (is_array($result) && array_key_exists('carrier_block', $result) && Module::isInstalled('germanext')) {
			require_once(_PS_MODULE_DIR_ . 'germanext/defines.php');
			
			$result['carrier_block'] = $this->context->smarty->fetch(GN_THEME_PATH . 'order-carrier.tpl');
		}
		
		return $result;
	}
}

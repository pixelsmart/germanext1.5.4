<?php
class AdminOrdersController extends AdminOrdersControllerCore
{
	public function postProcess()
	{
		if (Tools::isSubmit('submitAddPayment') && isset($order)) {
			if ($this->tabAccess['edit'] === '1') {
				$amount = str_replace(',', '.', Tools::getValue('payment_amount'));
				$currency = new Currency(Tools::getValue('payment_currency'));
				$order_has_invoice = $order->hasInvoice();
				
				if ($order_has_invoice) {
					$order_invoice = new OrderInvoice(Tools::getValue('payment_invoice'));
				}
				else {
					$order_invoice = null;
				}

				if ( ! Validate::isLoadedObject($order)) {
					$this->errors[] = Tools::displayError('Order can\'t be found');
				}
				elseif ( ! Validate::isNegativePrice($amount)) {
					$this->errors[] = Tools::displayError('Amount is invalid');
				}
				elseif ( ! Validate::isString(Tools::getValue('payment_method'))) {
					$this->errors[] = Tools::displayError('Payment method is invalid');
				}
				elseif ( ! Validate::isString(Tools::getValue('payment_transaction_id'))) {
					$this->errors[] = Tools::displayError('Transaction ID is invalid');
				}
				elseif ( ! Validate::isLoadedObject($currency)) {
					$this->errors[] = Tools::displayError('Currency is invalid');
				}
				elseif ($order_has_invoice && ! Validate::isLoadedObject($order_invoice)) {
					$this->errors[] = Tools::displayError('Invoice is invalid');
				}
				elseif ( ! Validate::isDate(Tools::getValue('payment_date'))) {
					$this->errors[] = Tools::displayError('Date is invalid');
				}
				else
				{
					if ( ! $order->addOrderPayment($amount, Tools::getValue('payment_method'), Tools::getValue('payment_transaction_id'), $currency, Tools::getValue('payment_date'), $order_invoice)) {
						$this->errors[] = Tools::displayError('An error occurred on adding order payment');
					}
					else {
						Tools::redirectAdmin(self::$currentIndex.'&id_order='.$order->id.'&vieworder&conf=4&token='.$this->token);
					}
				}
			}
			else {
				$this->errors[] = Tools::displayError('You do not have permission to edit here.');
			}
                
			AdminController::postProcess();
		}
		else {
			return parent::postProcess();
		}
	}
    
	public function renderView()
	{
		$order = new Order(Tools::getValue('id_order'));
		
		if ( ! Validate::isLoadedObject($order)) {
			throw new PrestaShopException('object can\'t be loaded');
		}

		$customer = new Customer($order->id_customer);
		$carrier  = new Carrier($order->id_carrier);
		$products = $this->getProducts($order);

		// Carrier module call
		$carrier_module_call = null;
		
		if ($carrier->is_module) {
			$module = Module::getInstanceByName($carrier->external_module_name);
			
			if (method_exists($module, 'displayInfoByCart')) {
				$carrier_module_call = call_user_func(array($module, 'displayInfoByCart'), $order->id_cart);
			}
		}

		// Retrieve addresses information
		$addressInvoice = new Address($order->id_address_invoice, $this->context->language->id);
		
		if (Validate::isLoadedObject($addressInvoice) && $addressInvoice->id_state) {
			$invoiceState = new State((int)$addressInvoice->id_state);
		}

		if ($order->id_address_invoice == $order->id_address_delivery) {
			$addressDelivery = $addressInvoice;
			
			if (isset($invoiceState)) {
				$deliveryState = $invoiceState;
			}
		}
		else {
			$addressDelivery = new Address($order->id_address_delivery, $this->context->language->id);
			
			if (Validate::isLoadedObject($addressDelivery) && $addressDelivery->id_state) {
				$deliveryState = new State((int)($addressDelivery->id_state));
			}
		}

		$this->toolbar_title = sprintf($this->l('Order #%1$d (%2$s) - %3$s %4$s'), $order->id, $order->reference, $customer->firstname, $customer->lastname);

		if (Shop::isFeatureActive()) {
			$shop = new Shop((int)$order->id_shop);
			$this->toolbar_title .= ' - '.sprintf($this->l('Shop: %s'), $shop->name);
		}
		
		// gets warehouses to ship products, if and only if advanced stock management is activated
		$warehouse_list = null;

		$order_details = $order->getOrderDetailList();
		
		foreach ($order_details as $order_detail) {
			$product = new Product($order_detail['product_id']);

			if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && $product->advanced_stock_management) {
				$warehouses = Warehouse::getWarehousesByProductId($order_detail['product_id'], $order_detail['product_attribute_id']);
				
				foreach ($warehouses as $warehouse) {
					if ( ! isset($warehouse_list[$warehouse['id_warehouse']])) {
						$warehouse_list[$warehouse['id_warehouse']] = $warehouse;
					}
				}
			}
		}

		$payment_methods = array();
		
		foreach (PaymentModule::getInstalledPaymentModules() as $payment) {
			$module = Module::getInstanceByName($payment['name']);
			
			if (Validate::isLoadedObject($module) && $module->active) {
				$payment_methods[$module->id] = $module->displayName;
			}
		}

		// display warning if there are products out of stock
		$display_out_of_stock_warning = false;
		$current_order_state = $order->getCurrentOrderState();
		
		if ( ! Validate::isLoadedObject($current_order_state) || ($current_order_state->delivery != 1 && $current_order_state->shipped != 1)) {
			$display_out_of_stock_warning = true;
		}

		// products current stock (from stock_available)
		foreach ($products as &$product) {
			$product['current_stock'] = StockAvailable::getQuantityAvailableByProduct($product['product_id'], $product['product_attribute_id'], $product['id_shop']);
			
			$resume = OrderSlip::getProductSlipResume((int)$product['id_order_detail']);
			$product['quantity_refundable'] = $product['product_quantity'] - $resume['product_quantity'];
			$product['amount_refundable'] = $product['total_price_tax_incl'] - $resume['amount_tax_incl'];
			$product['amount_refund'] = Tools::displayPrice($resume['amount_tax_incl']);
			$product['refund_history'] = OrderSlip::getProductSlipDetail($product['id_order_detail']);
			$product['return_history'] = OrderReturn::getProductReturnDetail($product['id_order_detail']);
			
			// if the current stock requires a warning
			if ($product['current_stock'] == 0 && $display_out_of_stock_warning) {
				$this->displayWarning($this->l('This product is out of stock: ').' '.$product['product_name']);
			}
		}

		// Smarty assign
		$this->tpl_view_vars = array(
			'order'                        => $order,
			'cart'                         => new Cart($order->id_cart),
			'customer'                     => $customer,
			'customer_addresses'           => $customer->getAddresses($this->context->language->id),
			'addresses'                    => array(
				'delivery'      => $addressDelivery,
				'deliveryState' => isset($deliveryState) ? $deliveryState : null,
				'invoice'       => $addressInvoice,
				'invoiceState'  => isset($invoiceState) ? $invoiceState : null
			),
			'customerStats'                => $customer->getStats(),
			'products'                     => $products,
			'discounts'                    => $order->getCartRules(),
			'orders_total_paid_tax_incl'   => $order->getOrdersTotalPaid(), // Get the sum of total_paid_tax_incl of the order with similar reference
			'total_paid'                   => $order->getTotalPaid(),
			'returns'                      => OrderReturn::getOrdersReturn($order->id_customer, $order->id),
			'customer_thread_message'      => CustomerThread::getCustomerMessages($order->id_customer, 0),
			'orderMessages'                => OrderMessage::getOrderMessages($order->id_lang),
			'messages'                     => Message::getMessagesByOrderId($order->id, true),
			'carrier'                      => new Carrier($order->id_carrier),
			'history'                      => $order->getHistory($this->context->language->id),
			'states'                       => OrderState::getOrderStates($this->context->language->id),
			'warehouse_list'               => $warehouse_list,
			'sources'                      => ConnectionsSource::getOrderSources($order->id),
			'currentState'                 => $order->getCurrentOrderState(),
			'currency'                     => new Currency($order->id_currency),
			'currencies'                   => Currency::getCurrencies(),
			'previousOrder'                => $order->getPreviousOrderId(),
			'nextOrder'                    => $order->getNextOrderId(),
			'current_index'                => self::$currentIndex,
			'carrierModuleCall'            => $carrier_module_call,
			'iso_code_lang'                => $this->context->language->iso_code,
			'id_lang'                      => $this->context->language->id,
			'can_edit'                     => ($this->tabAccess['edit'] == 1),
			'current_id_lang'              => $this->context->language->id,
			'invoices_collection'          => $order->getInvoicesCollection(),
			'not_paid_invoices_collection' => $order->getNotPaidInvoicesCollection(),
			'payment_methods'              => $payment_methods,
			'invoice_management_active'    => Configuration::get('PS_INVOICE', null, null, $order->id_shop),
			'order_payments'               => $order->getOrderPaymentCollection(),
			'total_payment_fees'           => $order->getTotalPaymentFees()
		);

		return AdminController::renderView();
	}
}

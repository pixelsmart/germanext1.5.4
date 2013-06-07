<?php
class OrderHistory extends OrderHistoryCore {
	public function changeIdOrderState($new_order_state, $id_order, $use_existing_payment = false) {
		if (!$new_order_state || !$id_order)
			return;

		if (!is_object($id_order) && is_numeric($id_order))
			$order = new Order((int)$id_order);
		elseif (is_object($id_order))
			$order = $id_order;
		else
			return;

		$new_os = new OrderState((int)$new_order_state, $order->id_lang);
		$old_os = $order->getCurrentOrderState();
		$is_validated = $this->isValidated();
		

		// executes hook
		if ($new_os->id == Configuration::get('PS_OS_PAYMENT')) {
			Hook::exec('actionPaymentConfirmation', array('id_order' => (int)$order->id));
		}

		// executes hook
		Hook::exec('actionOrderStatusUpdate', array(
			'newOrderStatus' => $new_os,
			'id_order' => (int)$order->id
		));

		if (Validate::isLoadedObject($order) && ($new_os instanceof OrderState))
		{
			// An email is sent the first time a virtual item is validated
			$virtual_products = $order->getVirtualProducts();
			if ($virtual_products && (((!$old_os || !$old_os->logable) && $new_os && $new_os->logable) || $order->total_paid <= 0))
			{
				$context = Context::getContext();
				$assign = array();
				foreach ($virtual_products as $key => $virtual_product)
				{
					$id_product_download = ProductDownload::getIdFromIdProduct($virtual_product['product_id']);
					$product_download = new ProductDownload($id_product_download);
					// If this virtual item has an associated file, we'll provide the link to download the file in the email
					if ($product_download->display_filename != '')
					{
						$assign[$key]['name'] = $product_download->display_filename;
						$dl_link = $product_download->getTextLink(false, $virtual_product['download_hash'])
							.'&id_order='.(int)$order->id
							.'&secure_key='.$order->secure_key;
						$assign[$key]['link'] = $dl_link;
						if ($virtual_product['date_expiration'] != '0000-00-00 00:00:00')
							$assign[$key]['deadline'] = Tools::displayDate($virtual_product['date_expiration '], $order->id_lang);
						if ($product_download->nb_downloadable != 0)
							$assign[$key]['downloadable'] = (int)$product_download->nb_downloadable;
					}
				}
								
				$customer = new Customer((int)$order->id_customer);
				
				$links = '<ul>';
				foreach($assign as $product)
				{
					$links .= '<li>';
					$links .= '<a href="'.$product['link'].'">'.Tools::htmlentitiesUTF8($product['name']).'</a>';
					if (isset($product['deadline']))
						$links .= '&nbsp;'.Tools::htmlentitiesUTF8(Tools::displayError('expires on')).'&nbsp;'.$product['deadline'];
					if (isset($product['downloadable']))
						$links .= '&nbsp;'.Tools::htmlentitiesUTF8(sprintf(Tools::displayError('downloadable %d time(s)'), (int)$product['downloadable']));	
					$links .= '</li>';
				}
				$links .= '<ul>';
				$data = array(
						'{lastname}' => $customer->lastname,
						'{firstname}' => $customer->firstname,
						'{id_order}' => (int)$order->id,
						'{order_name}' => $order->getUniqReference(),
						'{nbProducts}' => count($virtual_products),
						'{virtualProducts}' => $links
					);
				// If there's at least one downloadable file
				if (!empty($assign))
					Mail::Send((int)$order->id_lang, 'download_product', Mail::l('Virtual product to download', $order->id_lang), $data, $customer->email, $customer->firstname.' '.$customer->lastname,
						null, null, null, null, _PS_MAIL_DIR_, false, (int)$order->id_shop);
			}

			// @since 1.5.0 : gets the stock manager
			$manager = null;
			if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
				$manager = StockManagerFactory::getManager();
			// foreach products of the order
			foreach ($order->getProductsDetail() as $product)
			{
				// if becoming logable => adds sale
				if ($new_os->logable && !$old_os->logable)
				{
					ProductSale::addProductSale($product['product_id'], $product['product_quantity']);
					// @since 1.5.0 - Stock Management
					if (!Pack::isPack($product['product_id']) &&
						($old_os->id == Configuration::get('PS_OS_ERROR') || $old_os->id == Configuration::get('PS_OS_CANCELED')) &&
						!StockAvailable::dependsOnStock($product['id_product'], (int)$order->id_shop))
						StockAvailable::updateQuantity($product['product_id'], $product['product_attribute_id'], -(int)$product['product_quantity'], $order->id_shop);
				}
				// if becoming unlogable => removes sale
				elseif (!$new_os->logable && $old_os->logable)
				{
					ProductSale::removeProductSale($product['product_id'], $product['product_quantity']);

					// @since 1.5.0 - Stock Management
					if (!Pack::isPack($product['product_id']) &&
						($new_os->id == Configuration::get('PS_OS_ERROR') || $new_os->id == Configuration::get('PS_OS_CANCELED')) &&
						!StockAvailable::dependsOnStock($product['id_product']))
						StockAvailable::updateQuantity($product['product_id'], $product['product_attribute_id'], (int)$product['product_quantity'], $order->id_shop);
				}
				// if waiting for payment => payment error/canceled
				elseif (!$new_os->logable && !$old_os->logable &&
						 ($new_os->id == Configuration::get('PS_OS_ERROR') || $new_os->id == Configuration::get('PS_OS_CANCELED')) &&
						 !StockAvailable::dependsOnStock($product['id_product']))
						 StockAvailable::updateQuantity($product['product_id'], $product['product_attribute_id'], (int)$product['product_quantity'], $order->id_shop);
				// @since 1.5.0 : if the order is being shipped and this products uses the advanced stock management :
				// decrements the physical stock using $id_warehouse
				if ($new_os->shipped == 1 && $old_os->shipped == 0 &&
					Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') &&
					Warehouse::exists($product['id_warehouse']) &&
					$manager != null &&
					((int)$product['advanced_stock_management'] == 1 || Pack::usesAdvancedStockManagement($product['product_id'])))
				{
					// gets the warehouse
					$warehouse = new Warehouse($product['id_warehouse']);

					// decrements the stock (if it's a pack, the StockManager does what is needed)
					$manager->removeProduct(
						$product['product_id'],
						$product['product_attribute_id'],
						$warehouse,
						$product['product_quantity'],
						Configuration::get('PS_STOCK_CUSTOMER_ORDER_REASON'),
						true,
						(int)$order->id
					);
				}
				// @since.1.5.0 : if the order was shipped, and is not anymore, we need to restock products
				elseif ($new_os->shipped == 0 && $old_os->shipped == 1 &&
						 Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') &&
						 Warehouse::exists($product['id_warehouse']) &&
						 $manager != null &&
						 ((int)$product['advanced_stock_management'] == 1 || Pack::usesAdvancedStockManagement($product['product_id'])))
				{
					// if the product is a pack, we restock every products in the pack using the last negative stock mvts
					if (Pack::isPack($product['product_id']))
					{
						$pack_products = Pack::getItems($product['product_id'], Configuration::get('PS_LANG_DEFAULT', null, null, $order->id_shop));
						foreach ($pack_products as $pack_product)
						{
							if ($pack_product->advanced_stock_management == 1)
							{
								$mvts = StockMvt::getNegativeStockMvts($order->id, $pack_product->id, 0, $pack_product->pack_quantity * $product['product_quantity']);
								foreach ($mvts as $mvt)
								{
									$manager->addProduct(
										$pack_product->id,
										0,
										new Warehouse($mvt['id_warehouse']),
										$mvt['physical_quantity'],
										null,
										$mvt['price_te'],
										true
									);
								}
								if (!StockAvailable::dependsOnStock($product['id_product']))
									StockAvailable::updateQuantity($pack_product->id, 0, (int)$pack_product->pack_quantity * $product['product_quantity'], $order->id_shop);
							}
						}
					}
					// else, it's not a pack, re-stock using the last negative stock mvts
					else
					{
						$mvts = StockMvt::getNegativeStockMvts($order->id, $product['product_id'], $product['product_attribute_id'], $product['product_quantity']);
						foreach ($mvts as $mvt)
						{
							$manager->addProduct(
								$product['product_id'],
								$product['product_attribute_id'],
								new Warehouse($mvt['id_warehouse']),
								$mvt['physical_quantity'],
								null,
								$mvt['price_te'],
								true
							);
						}
					}
				}
			}
		}

		$this->id_order_state = (int)$new_order_state;
		
		// changes invoice number of order ?
		if (!Validate::isLoadedObject($new_os) || !Validate::isLoadedObject($order))
			die(Tools::displayError('Invalid new order state'));

		// the order is valid if and only if the invoice is available and the order is not cancelled
		$order->current_state = $this->id_order_state;
		$order->valid = $new_os->logable;
		$order->update();

		if ($new_os->invoice && !$order->invoice_number)
			$order->setInvoice($use_existing_payment);

		// set orders as paid
		if ($new_os->paid == 1)
		{
			$invoices = $order->getInvoicesCollection();
			if ($order->total_paid != 0)
				$payment_method = Module::getInstanceByName($order->module);

			foreach ($invoices as $invoice)
			{
				$rest_paid = $invoice->getRestPaid();
				if ($rest_paid > 0)
				{
					$payment = new OrderPayment();
					$payment->order_reference = $order->reference;
					$payment->id_currency = $order->id_currency;
					$payment->amount = $rest_paid;

					if ($order->total_paid != 0)
						$payment->payment_method = $payment_method->displayName;
					else 
						$payment->payment_method = null;
					
					// Update total_paid_real value for backward compatibility reasons
					if ($payment->id_currency == $order->id_currency)
						$order->total_paid_real += $payment->amount;
					else
						$order->total_paid_real += Tools::ps_round(Tools::convertPrice($payment->amount, $payment->id_currency, false), 2);
					$order->save();
						
					$payment->conversion_rate = 1;
					$payment->save();
					Db::getInstance()->execute('
					INSERT INTO `'._DB_PREFIX_.'order_invoice_payment`
					VALUES('.(int)$invoice->id.', '.(int)$payment->id.', '.(int)$order->id.')');
				}
			}
		}

		// updates delivery date even if it was already set by another state change
		if ($new_os->delivery)
			$order->setDelivery();

		// executes hook
		Hook::exec('actionOrderStatusPostUpdate', array(
			'newOrderStatus' => $new_os,
			'id_order' => (int)$order->id,
		));
	}
}

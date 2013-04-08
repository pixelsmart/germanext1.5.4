<?php
class Order extends OrderCore
{
	public $total_payment_tax_excl;
    public $total_payment_tax_incl;
	public $payment_message;
    
	public static $definition = array(
		'table' => 'orders',
		'primary' => 'id_order',
		'fields' => array(
			'id_address_delivery' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_address_invoice' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_cart' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_currency' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_shop_group' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_shop' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'id_lang' => 					array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_customer' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'id_carrier' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
			'current_state' => 				array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
			'secure_key' => 				array('type' => self::TYPE_STRING, 'validate' => 'isMd5'),
			'payment' => 					array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
			'module' => 					array('type' => self::TYPE_STRING),
            'total_payment_tax_excl' =>     array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'total_payment_tax_incl' =>     array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'payment_message' =>            array('type' => self::TYPE_STRING, 'validate' => 'isGenericName'),
			'recyclable' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'gift' => 						array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'gift_message' => 				array('type' => self::TYPE_STRING, 'validate' => 'isMessage'),
			'mobile_theme' => 				array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'total_discounts' =>			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_discounts_tax_incl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_discounts_tax_excl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_paid' => 				array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
			'total_paid_tax_incl' => 		array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_paid_tax_excl' => 		array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_paid_real' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
			'total_products' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
			'total_products_wt' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true),
			'total_shipping' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_shipping_tax_incl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_shipping_tax_excl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'carrier_tax_rate' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
			'total_wrapping' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_wrapping_tax_incl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'total_wrapping_tax_excl' =>	array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
			'shipping_number' => 			array('type' => self::TYPE_STRING, 'validate' => 'isTrackingNumber'),
			'conversion_rate' => 			array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
			'invoice_number' => 			array('type' => self::TYPE_INT),
			'delivery_number' => 			array('type' => self::TYPE_INT),
			'invoice_date' => 				array('type' => self::TYPE_DATE),
			'delivery_date' => 				array('type' => self::TYPE_DATE),
			'valid' => 						array('type' => self::TYPE_BOOL),
			'reference' => 					array('type' => self::TYPE_STRING),
			'date_add' => 					array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
			'date_upd' => 					array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
		),
	);
	
	public function getOrderTaxes($products = false, $slip = false)
	{
		if ( ! $products)
        {
			$products = ($slip && Validate::isLoadedObject($slip)) ? $slip->getProducts() : $this->getProducts();
        }
		
		$taxes            = array();
		$id_order_details = array();
		$defTaxName       = null;
		$defTaxRate       = 0;
		
		if (is_array($products) && sizeof($products))
		{
			foreach ($products as $product)
			{
				array_push($id_order_details, (int)$product['id_order_detail']);
			}
		}
		
		if (sizeof($id_order_details))
		{
			$order_taxes = Db::getInstance()->ExecuteS('
				SELECT
					od.*,
					odt.*,
					t.`rate`,
					tl.`name`
				FROM
					`' . _DB_PREFIX_ . 'order_detail` od
				LEFT JOIN
					`' . _DB_PREFIX_ . 'order_detail_tax` odt ON
					(
						od.`id_order_detail` = odt.`id_order_detail`
					)
				LEFT JOIN
					`' . _DB_PREFIX_ . 'tax` t ON
					(
						t.`id_tax` = odt.`id_tax`
					)
				LEFT JOIN
					`' . _DB_PREFIX_ . 'tax_lang` tl ON
					(
						t.`id_tax` = tl.`id_tax` AND
						tl.`id_lang` = ' . (int)$this->id_lang . '
					)
				WHERE odt.`id_order_detail` IN (' . (implode(',', $id_order_details)) . ')
			');
			
			if ($order_taxes && sizeof($order_taxes))
			{
				foreach ($order_taxes as $order_tax)
				{
					if ( ! array_key_exists($order_tax['name'], $taxes))
                    {
						$taxes[$order_tax['name']] = array(
							'total'      => 0,
							'total_net'  => 0,
							'total_vat'  => 0,
							'rate'       => (float)$order_tax['rate'],
							'percentage' => 0
						);
                    }

					$taxes[$order_tax['name']]['total_net']+= (float)$order_tax['total_price_tax_excl'];
					$taxes[$order_tax['name']]['total_vat']+= (float)$order_tax['total_price_tax_incl'];
					$taxes[$order_tax['name']]['total']+= $order_tax['total_price_tax_incl'] - (float)$order_tax['total_price_tax_excl'];
				}
			}
		}
		
		if (sizeof($taxes))
		{
			foreach ($taxes as &$tax)
			{
				$tax['percentage'] = 100 / ($this->total_products_wt / $tax['total_vat']);
			}

			return $taxes;
		}
		
		return false;
	}
	
	public static function calculateCompundTax($price, $order_taxes)
	{
		if ( ! is_array($order_taxes) || ( ! Validate::isPrice($price) && ! Validate::isNegativePrice($price)))
		{
			return false;
		}
		
		$result = array();
		
		foreach ($order_taxes as $tax_name => $tax_details)
		{
			if (array_key_exists('percentage', $tax_details))
			{
				$rate      = (float)$tax_details['rate'];
				$total_vat = $price * ($tax_details['percentage'] / 100);
				$total_net = $total_vat / (1 + $rate / 100);
				$total     = $total_vat - $total_net;
				
				$result[$tax_name] = array(
					'total'     => $total,
					'total_net' => $total_net,
					'total_vat' => $total_vat,
				);
			}
		}
		
		return sizeof($result) ? $result : false;
	}
	
	public static function calculateCompundTaxPrice($price, $order_taxes, $add_tax = false)
	{
		if ($taxes = self::calculateCompundTax($price, $order_taxes))
		{
			foreach ($taxes as $compound_tax)
			{
				if ($add_tax)
				{
					$price+= (float)$compound_tax['total'];
				}
				else
				{
					$price-= (float)$compound_tax['total'];
				}
			}
		}
		
		return Tools::ps_round((float)$price, 2);
	}

	public static function addCompoundTaxesToTaxArray(&$taxes, $prices)
	{
		if ( ! is_array($taxes) || ! is_array($prices))
		{
			return false;
		}
		
		$compound_taxes = array();
		
		foreach ($prices as $price)
		{
			if ( ! Validate::isPrice($price) && ! Validate::isNegativePrice($price))
			{
				continue;
			}

			array_push($compound_taxes, self::calculateCompundTax($price, $taxes));
		}

		foreach ($compound_taxes as $price_compund)
		{
			if ( ! $price_compund)
			{
				continue;
			}
			
			foreach ($price_compund as $compound_tax_name => $compound_tax)
			{
				if (array_key_exists($compound_tax_name, $taxes))
				{
					foreach ($compound_tax as $k => $v)
					{
						if (array_key_exists($k, $taxes[$compound_tax_name]))
						{
							$taxes[$compound_tax_name][$k]+= $v;
						}
					}
				}
			}
		}
	}
    
	public function getOrderTaxDetails($products = false, $slip = false)
	{
		if ( ! $products)
        {
			$products = ($slip && Validate::isLoadedObject($slip)) ? $slip->getProducts() : $this->getProducts();
        }
		
		$taxes      = array();
		$defTaxName = null;
		$defTaxRate = 0;
		
		$order_taxes = $this->getOrderTaxes($products, $slip);

		if ($order_taxes)
		{
			if ( ! $slip)
			{
				$paymentNet = 0;
				$paymentVat = 0;
				$has_payments = false;
				
				$invoices = $this->getOrderPaymentCollection()->getResults();

				if ($invoices && sizeof($invoices))
				{
					foreach ($invoices as $invoice)
					{
						$paymentNet+= (float)$invoice->total_payment_tax_excl;
						$paymentVat+= (float)$invoice->total_payment_tax_incl;
						
						if ( ! $has_payments)
						{
							$has_payments = true;
						}
					}
				}
				
				if ( ! $has_payments)
				{
					$paymentNet+= (float)$this->total_payment_tax_excl;
					$paymentVat+= (float)$this->total_payment_tax_incl;
				}

				self::addCompoundTaxesToTaxArray($order_taxes, array($this->total_shipping_tax_incl, $this->total_wrapping_tax_incl, -abs($this->total_discounts_tax_incl), $paymentVat));
			}
		}

		return $order_taxes;
	}
	
	public function getOrderPaymentFeeDetails($amount_paid, $payment_module = false, $products = false)
	{
		$module = false;
		$result = array(
			'payment' => false,
			'module' => false,
			'payment_message' => '',
			'total_payment_tax_incl' => 0,
			'total_payment_tax_excl' => 0
		);

		if ( ! $payment_module)
		{
			$payment_module = $this->payment;
			
			$module = Module::getInstanceByName($payment_module);
		}
		else
		{
			if (Validate::isUnsignedId($payment_module))
			{
				$module = Module::getInstanceById((int)$payment_module);
			}
			else
			{
				$module = Module::getInstanceByName($payment_module);
			}
		}
		
		if ($module && Validate::isLoadedObject($module))
		{
            $result['payment'] = $module->displayName;
            $result['module'] = $module->name;
			
			$id_payment = PaymentCost::getPaymentIdByModuleId($module->id);
			
			if ($id_payment)
			{
				if ($products && is_array($products))
				{
					$order_taxes = Cart::getTaxDetails($products);
				}
				else
				{
					$order_taxes = $this->getOrderTaxes();
				}

				$total_payment = PaymentCost::s_getPriceImpact($id_payment, $amount_paid);

				$result['payment_message'] = PaymentCost::getFeeTitle($id_payment, (int)$this->id_lang);
				$result['total_payment_tax_incl'] = $total_payment;
				$result['total_payment_tax_excl'] = Order::calculateCompundTaxPrice($total_payment, $order_taxes);

				return $result;
			}
		}
		
		return false;
	}
	
	public function addOrderPayment($amount_paid, $payment_method = null, $payment_transaction_id = null, $currency = null, $date = null, $order_invoice = null)
	{
		$order_payment = new OrderPayment();
		$order_payment->order_reference = $this->reference;
		$order_payment->id_currency = ($currency ? $currency->id : $this->id_currency);
		// we kept the currency rate for historization reasons
		$order_payment->conversion_rate = ($currency ? $currency->conversion_rate : 1);
		// if payment_method is define, we used this
		$order_payment->transaction_id = $payment_transaction_id;
		$order_payment->amount = $amount_paid;
		$order_payment->date_add = ($date ? $date : null);
		
		if (Validate::isUnsignedId($payment_method))
		{
			$fee_details = $this->getOrderPaymentFeeDetails($this->total_products_wt + $this->total_shipping_tax_incl + $this->total_wrapping_tax_incl - abs($this->total_discounts_tax_incl), $payment_method);
			
			if ($fee_details)
			{
				foreach ($fee_details as $property => $value)
				{
					if (property_exists($order_payment, $property))
					{
						$order_payment->{$property} = $value;
					}
				}
				
				$order_payment->payment_method = $fee_details['payment'];
			}
		}
		else
		{
			$order_payment->payment_method = ($payment_method ? $payment_method : $this->payment);
		}

		// Update total_paid_real value for backward compatibility reasons
		if ($order_payment->id_currency == $this->id_currency)
			$this->total_paid_real += $order_payment->amount;
		else
			$this->total_paid_real += Tools::ps_round(Tools::convertPrice($order_payment->amount, $order_payment->id_currency, false), 2);

		// We put autodate parameter of add method to true if date_add field is null
		$res = $order_payment->add(is_null($order_payment->date_add)) && $this->update();
		
		if (!$res)
			return false;
	
		if (!is_null($order_invoice))
		{
			$res = Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'order_invoice_payment`
			VALUES('.(int)$order_invoice->id.', '.(int)$order_payment->id.', '.(int)$this->id.')');
		}
		
		return $res;
	}
	
	public function getTotalPaymentFees($tax = true)
	{
		$payments = $this->getOrderPaymentCollection();

		$total = 0;
		$property = 'total_payment_tax_incl';
		
		if ( ! $tax)
		{
			$property = 'total_payment_tax_excl';
		}
		
		if (sizeof($payments) > 0)
		{
			foreach ($payments as $payment)
			{
				$total+= (float)$payment->{$property};
			}
		}
		else
		{
			$total+= (float)$this->{$property};
		}
		
		return $total;
	}
}


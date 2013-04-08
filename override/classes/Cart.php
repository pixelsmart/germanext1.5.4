<?php
class Cart extends CartCore
{
    public $id_payment;
    public $recyclable = 0;
    
    const ONLY_PAYMENT = 9;
    
    public function __construct($id = null, $id_lang = null)
    {
        if ( ! array_key_exists('id_payment', self::$definition['fields'])) {
            self::$definition['fields']['id_payment'] = array('type' => parent::TYPE_INT, 'validate' => 'isUnsignedId');
        }
        
        return parent::__construct($id, $id_lang);
    }
	
    public function getFields()
    {
	parent::validateFields();

	$fields['id_payment'] = (int)($this->id_payment);
    
	return parent::getFields();
    }
    
    public function getOrderTotal($with_taxes = true, $type = Cart::BOTH, $products = null, $id_carrier = null, $use_cache = true)
    {
	if ( ! $this->id) {
	    return 0;
	}

	$type = (int)$type;
	$array_type = array(
		Cart::ONLY_PRODUCTS,
		Cart::ONLY_DISCOUNTS,
		Cart::BOTH,
		Cart::BOTH_WITHOUT_SHIPPING,
		Cart::ONLY_SHIPPING,
		Cart::ONLY_WRAPPING,
		Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING,
		Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING,
		Cart::ONLY_PAYMENT
	);
	    
	$taxes = $this->getTaxDetails();
	$order_total_products_taxed = 0;
	    
	// Define virtual context to prevent case where the cart is not the in the global context
	$virtual_context = Context::getContext()->cloneContext();
	$virtual_context->cart = $this;

	if ( ! in_array($type, $array_type)) {
	    die(Tools::displayError());
	}
		    
	$with_shipping = in_array($type, array(Cart::BOTH, Cart::ONLY_SHIPPING));

	// if cart rules are not used
	if ($type == Cart::ONLY_DISCOUNTS && !CartRule::isFeatureActive()) {
	    return 0;
	}

	// no shipping cost if is a cart with only virtuals products
	$virtual = $this->isVirtualCart();
	
	if ($virtual && $type == Cart::ONLY_SHIPPING) {
	    return 0;
	}

	if ($virtual && $type == Cart::BOTH)
		$type = Cart::BOTH_WITHOUT_SHIPPING;

	if ( ! in_array($type, array(Cart::BOTH_WITHOUT_SHIPPING, Cart::ONLY_PRODUCTS,  Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING))) {
	    if (is_null($products) && is_null($id_carrier)) {
		$shipping_fees_taxed = $this->getTotalShippingCost(null, (boolean)$with_taxes);
	    }
	    else {
		$shipping_fees_taxed = $this->getPackageShippingCost($id_carrier, (int)$with_taxes, null, $products);
	    }
	}
	else {
	    $shipping_fees_taxed = 0;
	}

	$shipping_fees = Order::calculateCompundTaxPrice($shipping_fees_taxed, $taxes);
	
	if ($with_taxes) {
	    $shipping_fees = $shipping_fees_taxed;
	}

	if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING) {
	    $type = Cart::ONLY_PRODUCTS;
	}

	if (is_null($products)) {
	    $products = $this->getProducts();
	}

	if ($type == Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING) {
	    foreach ($products as $key => $product) {
		if ($product['is_virtual']) {
		    unset($products[$key]);
		}
	    }
	    
	    $type = Cart::ONLY_PRODUCTS;
	}

	$order_total = 0;
	
	if (Tax::excludeTaxeOption()) {
	    $with_taxes = false;
	}

	foreach ($products as $product)
	{
	    if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
		$address_id = (int)$this->id_address_invoice;
	    }
	    else {
		$address_id = (int)$product['id_address_delivery'];
	    }
	    
	    if ( ! Address::addressExists($address_id)) {
		$address_id = null;
	    }
		
	    if ($this->_taxCalculationMethod == PS_TAX_EXC)
	    {
		// Here taxes are computed only once the quantity has been applied to the product price
		$price = Product::getPriceStatic(
		    (int)$product['id_product'],
		    false,
		    (int)$product['id_product_attribute'],
		    2,
		    null,
		    false,
		    true,
		    $product['cart_quantity'],
		    false,
		    (int)$this->id_customer ? (int)$this->id_customer : null,
		    (int)$this->id,
		    $address_id
		);

		$total_ecotax = $product['ecotax'] * (int)$product['cart_quantity'];
		$total_price = $price * (int)$product['cart_quantity'];
		    
		$product_tax_rate = (float)Tax::getProductTaxRate((int)$product['id_product'], (int)$address_id);
		$product_eco_tax_rate = Tax::getProductEcotaxRate((int)$address_id);
		$total_ecotax = $total_ecotax * (1 + $product_eco_tax_rate / 100);
		$order_total_products_taxed+= ($total_price - $total_ecotax) * (1 + $product_tax_rate / 100);

		if ($with_taxes)
		{
		    $total_price = ($total_price - $total_ecotax) * (1 + $product_tax_rate / 100);
		    $total_price = Tools::ps_round($total_price + $total_ecotax, 2);
		}
	    }
	    else
	    {
		$price = Product::getPriceStatic(
		    (int)$product['id_product'],
		    true,
		    (int)$product['id_product_attribute'],
		    2,
		    null,
		    false,
		    true,
		    $product['cart_quantity'],
		    false,
		    ((int)$this->id_customer ? (int)$this->id_customer : null),
		    (int)$this->id,
		    ((int)$address_id ? (int)$address_id : null),
		    $null,
		    true,
		    true,
		    $virtual_context
		);

		$total_price = Tools::ps_round($price, 2) * (int)$product['cart_quantity'];
		$order_total_products_taxed+= Tools::ps_round($total_price, 2);

		if ( ! $with_taxes)
		{
		    $product_tax_rate = (float)Tax::getProductTaxRate((int)$product['id_product'], (int)$address_id);
		    $total_price = Tools::ps_round($total_price / (1 + ($product_tax_rate / 100)), 2);
		}
	    }
		
	    $order_total += $total_price;
	}

	$order_total_products = $order_total;

	if ($type == Cart::ONLY_DISCOUNTS) {
	    $order_total = 0;
	}

	// Wrapping Fees
	$wrapping_fees = 0;
	$wrapping_fees_taxed = 0;

	if ($this->gift)
	{
	    $wrapping_fees_taxed = (float)Configuration::get('PS_GIFT_WRAPPING_PRICE');
	    $wrapping_fees  = Order::calculateCompundTaxPrice($wrapping_fees_taxed, $taxes);

	    if ($with_taxes)
	    {
		$wrapping_fees = $wrapping_fees_taxed;
	    }

	    $wrapping_fees = Tools::convertPrice(Tools::ps_round($wrapping_fees, 2), Currency::getCurrencyInstance((int)$this->id_currency));
	}

	$order_total_discount = 0;
	$order_total_discount_taxed = 0;
	    
	if ( ! in_array($type, array(Cart::ONLY_SHIPPING, Cart::ONLY_PRODUCTS)) && CartRule::isFeatureActive()) {
	    // First, retrieve the cart rules associated to this "getOrderTotal"
	    if ($with_shipping || $type == Cart::ONLY_DISCOUNTS) {
		    $cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_ALL);
	    }
	    else {
		$cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_REDUCTION);
		// Cart Rules array are merged manually in order to avoid doubles
		foreach ($this->getCartRules(CartRule::FILTER_ACTION_GIFT) as $tmp_cart_rule) {
		    $flag = false;
		    
		    foreach ($cart_rules as $cart_rule) {
			if ($tmp_cart_rule['id_cart_rule'] == $cart_rule['id_cart_rule']) {
				$flag = true;
			}
		    }
		    
		    if ( ! $flag) {
			$cart_rules[] = $tmp_cart_rule;
		    }
		}
	    }
	    
	    $id_address_delivery = 0;
	    
	    if (isset($products[0])) {
		$id_address_delivery = (is_null($products) ? $this->id_address_delivery : $products[0]['id_address_delivery']);
	    }
		
	    $package = array('id_carrier' => $id_carrier, 'id_address' => $id_address_delivery, 'products' => $products);
	    
	    // Then, calculate the contextual value for each one
	    foreach ($cart_rules as $cart_rule) {
		// If the cart rule offers free shipping, add the shipping cost
		if (($with_shipping || $type == Cart::ONLY_DISCOUNTS) && $cart_rule['obj']->free_shipping) {
		    $order_total_discount += Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_SHIPPING, ($param_product ? $package : null), $use_cache), 2);
		    $order_total_discount_taxed += Tools::ps_round($cart_rule['obj']->getContextualValue(true, $virtual_context, CartRule::FILTER_ACTION_SHIPPING, ($param_product ? $package : null), $use_cache), 2);
		}

		// If the cart rule is a free gift, then add the free gift value only if the gift is in this package
		if ((int)$cart_rule['obj']->gift_product)
		{
		    $in_order = false;
		    
		    if (is_null($products)) {
			$in_order = true;
		    }
		    else {
			foreach ($products as $product) {
			    if ($cart_rule['obj']->gift_product == $product['id_product'] && $cart_rule['obj']->gift_product_attribute == $product['id_product_attribute']) {
				$in_order = true;
			    }
			}
		    }

		    if ($in_order) {
			$order_total_discount += $cart_rule['obj']->getContextualValue($with_taxes, null, CartRule::FILTER_ACTION_GIFT, $package, $use_cache);
			$order_total_discount_taxed += $cart_rule['obj']->getContextualValue(true, null, CartRule::FILTER_ACTION_GIFT, $package, $use_cache);
		    }
		}

		// If the cart rule offers a reduction, the amount is prorated (with the products in the package)
		if ($cart_rule['obj']->reduction_percent > 0 || $cart_rule['obj']->reduction_amount > 0) {
		    $order_total_discount += Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_REDUCTION, $package, $use_cache), 2);
		    $order_total_discount_taxed += Tools::ps_round($cart_rule['obj']->getContextualValue(true, $virtual_context, CartRule::FILTER_ACTION_REDUCTION, $package, $use_cache), 2);
		}
	    }
		
	    $order_total_discount = min(Tools::ps_round($order_total_discount, 2), $wrapping_fees + $order_total_products + $shipping_fees);
	    $order_total_discount_taxed = min(Tools::ps_round($order_total_discount_taxed, 2), $wrapping_fees_taxed + $order_total_products_taxed + $shipping_fees_taxed);
	    $order_total -= $order_total_discount;
	}

	$payment_fees_taxed = (float)($this->id_payment) ? PaymentCost::s_getPriceImpact($this->id_payment, ($order_total_products_taxed + $shipping_fees_taxed + $wrapping_fees_taxed - abs($order_total_discount_taxed))) : 0;
	$payment_fees = Order::calculateCompundTaxPrice($payment_fees_taxed, $taxes);
	    
	if ($with_taxes)
	{
	    $payment_fees = $payment_fees_taxed;
	}
	    
	Order::addCompoundTaxesToTaxArray($taxes, array($shipping_fees_taxed, $wrapping_fees_taxed, -abs($order_total_discount_taxed), $payment_fees_taxed));
    
	if ($type == Cart::ONLY_PAYMENT) {
	    return $payment_fees;
	}

	if ($type == Cart::ONLY_SHIPPING) {
	    return $shipping_fees;
	}

	if ($type == Cart::ONLY_WRAPPING) {
	    return $wrapping_fees;
	}

	if ($type == Cart::BOTH) {
	    $order_total += $shipping_fees + $wrapping_fees + $payment_fees;
	}

	if ($order_total < 0 && $type != Cart::ONLY_DISCOUNTS) {
	    return 0;
	}

	if ($type == Cart::ONLY_DISCOUNTS) {
	    return $order_total_discount;
	}

	return Tools::ps_round((float)$order_total, 2);
    }
	
    public function getTaxDetails($products = false)
    {
	if ( ! is_array($products) || ! sizeof($products)) {
	    $products = $this->getProducts();
	}
		
	$context = Context::getContext();

	if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
	    $address = Address::initialize((int)$this->id_address_invoice);
	}
	else {
	    $address = Address::initialize((int)$this->id_address_delivery);
	}

	if ( ! sizeof($products)) {
	    return false;
	}
		
	$prepared_taxes = array();
	$total_products_price = 0;
		
	foreach ($products as $product)
	{
	    $id_tax_rules = (int)Product::getIdTaxRulesGroupByIdProduct((int)$product['id_product'], $context);
	    $tax_manager = TaxManagerFactory::getManager($address, $id_tax_rules);
	    $tax_calculator = $tax_manager->getTaxCalculator();
	    
	    $product_taxes = $tax_calculator->getTaxData($product['price']);
	    $total_products_price+= (float)$product['total_wt'];
	    
	    foreach ($product_taxes as $tax_id => $tax_data)
	    {
		if ( ! array_key_exists($tax_id, $prepared_taxes))
		{
		    $prepared_taxes[$tax_id] = $tax_data + array(
			'total' => (float)$product['total_wt'] - (float)$product['total'],
			'total_net' => (float)$product['total'],
			'total_vat' => (float)$product['total_wt'],
			'percentage' => 0
		    );
		}
		else
		{
		    $prepared_taxes[$tax_id]['total']+= ((float)$product['total_wt'] - (float)$product['total']);
		    $prepared_taxes[$tax_id]['total_net']+= (float)$product['total'];
		    $prepared_taxes[$tax_id]['total_vat']+= (float)$product['total_wt'];
		}
	    }
	}
		
	foreach ($prepared_taxes as &$tax)
	{
	    $tax['percentage'] = 100 / ($total_products_price / $tax['total_vat']);
	}
		
	return sizeof($prepared_taxes) ? $prepared_taxes : false;
    }
    
    public function getPackageShippingCost($id_carrier = null, $use_tax = true, Country $default_country = null, $product_list = null, $id_zone = null)
    {
	if ($this->isVirtualCart()) {
	    return 0;
	}

	if ( ! $default_country) {
	    $default_country = Context::getContext()->country;
	}

	$complete_product_list = $this->getProducts();
	
	if (is_null($product_list)) {
	    $products = $complete_product_list;
	}
	else {
	    $products = $product_list;
	}

	if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
	    $address_id = (int)$this->id_address_invoice;
	}
	elseif (count($product_list)) {
	    $prod = current($product_list);
	    $address_id = (int)$prod['id_address_delivery'];
	}
	else {
	    $address_id = null;
	}
	
	if ( ! Address::addressExists($address_id)) {
	    $address_id = null;
	}

	$cache_id = 'getPackageShippingCost_'.(int)$this->id.'_'.(int)$address_id.'_'.(int)$id_carrier.'_'.(int)$use_tax.'_'.(int)$default_country->id;
		
	if ($products) {
	    foreach ($products as $product) {
		    $cache_id .= '_'.(int)$product['id_product'].'_'.(int)$product['id_product_attribute'];
	    }
	}
		
	if (Cache::isStored($cache_id)) {
	    return Cache::retrieve($cache_id);
	}
		
	// Order total in default currency without fees
	$order_total = $this->getOrderTotal(true, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING, $product_list);

	// Start with shipping cost at 0
	$shipping_cost = 0;

	// If no product added, return 0
	if ( ! count($products)) {
	    Cache::store($cache_id, $shipping_cost);
	    return $shipping_cost;
	}

	if( ! isset($id_zone))
	{
	    // Get id zone
	    if ( ! $this->isMultiAddressDelivery()
		&& isset($this->id_address_delivery) // Be carefull, id_address_delivery is not usefull one 1.5
		&& $this->id_address_delivery
		&& Customer::customerHasAddress($this->id_customer, $this->id_address_delivery
	    )) {
		$id_zone = Address::getZoneById((int)$this->id_address_delivery);
	    }
	    else {
		if ( ! Validate::isLoadedObject($default_country)) {
		    $default_country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), Configuration::get('PS_LANG_DEFAULT'));
		}

		$id_zone = (int)$default_country->id_zone;
	    }
	}

	if ($id_carrier && ! $this->isCarrierInRange((int)$id_carrier, (int)$id_zone)) {
	    $id_carrier = '';
	}

	if (empty($id_carrier) && $this->isCarrierInRange((int)Configuration::get('PS_CARRIER_DEFAULT'), (int)$id_zone)) {
	    $id_carrier = (int)Configuration::get('PS_CARRIER_DEFAULT');
	}

	if (empty($id_carrier)) {
	    if ((int)$this->id_customer) {
		$customer = new Customer((int)$this->id_customer);
		$result = Carrier::getCarriers((int)Configuration::get('PS_LANG_DEFAULT'), true, false, (int)$id_zone, $customer->getGroups());
		unset($customer);
	    }
	    else {
		$result = Carrier::getCarriers((int)Configuration::get('PS_LANG_DEFAULT'), true, false, (int)$id_zone);
	    }

	    foreach ($result as $k => $row) {
		if ($row['id_carrier'] == Configuration::get('PS_CARRIER_DEFAULT')) {
		    continue;
		}

		if ( ! isset(self::$_carriers[$row['id_carrier']])) {
		    self::$_carriers[$row['id_carrier']] = new Carrier((int)$row['id_carrier']);
		}

		$carrier = self::$_carriers[$row['id_carrier']];

		// Get only carriers that are compliant with shipping method
		if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT && $carrier->getMaxDeliveryPriceByWeight((int)$id_zone) === false)
		    || ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE && $carrier->getMaxDeliveryPriceByPrice((int)$id_zone) === false)) {
		    unset($result[$k]);
		    continue;
		}

		// If out-of-range behavior carrier is set on "Desactivate carrier"
		if ($row['range_behavior']) {
		    $check_delivery_price_by_weight = Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $this->getTotalWeight(), (int)$id_zone);

		    $total_order = $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, $product_list);
		    $check_delivery_price_by_price = Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $total_order, (int)$id_zone, (int)$this->id_currency);

		    // Get only carriers that have a range compatible with cart
		    if (($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT && !$check_delivery_price_by_weight)
			|| ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE && !$check_delivery_price_by_price)) {
			unset($result[$k]);
			continue;
		    }
		}

		if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT) {
		    $shipping = $carrier->getDeliveryPriceByWeight($this->getTotalWeight($product_list), (int)$id_zone);
		}
		else {
		    $shipping = $carrier->getDeliveryPriceByPrice($order_total, (int)$id_zone, (int)$this->id_currency);
		}

		if ( ! isset($min_shipping_price)) {
		    $min_shipping_price = $shipping;
		}

		if ($shipping <= $min_shipping_price) {
		    $id_carrier = (int)$row['id_carrier'];
		    $min_shipping_price = $shipping;
		}
	    }
	}

	if (empty($id_carrier)) {
	    $id_carrier = Configuration::get('PS_CARRIER_DEFAULT');
	}

	if ( ! isset(self::$_carriers[$id_carrier])) {
	    self::$_carriers[$id_carrier] = new Carrier((int)$id_carrier, Configuration::get('PS_LANG_DEFAULT'));
	}

	$carrier = self::$_carriers[$id_carrier];

	if ( ! Validate::isLoadedObject($carrier)) {
	    Cache::store($cache_id, 0);
	    return 0;
	}

	if ( ! $carrier->active) {
	    Cache::store($cache_id, $shipping_cost);
	    return $shipping_cost;
	}

	// Free fees if free carrier
	if ($carrier->is_free == 1) {
	    Cache::store($cache_id, 0);
	    return 0;
	}
		
	// Select carrier tax
	if ($use_tax && !Tax::excludeTaxeOption()) {
	    $address = Address::initialize((int)$address_id);
	    $carrier_tax = $carrier->getTaxesRate($address);
	}

	$configuration = Configuration::getMultiple(array(
	    'PS_SHIPPING_FREE_PRICE',
	    'PS_SHIPPING_HANDLING',
	    'PS_SHIPPING_METHOD',
	    'PS_SHIPPING_FREE_WEIGHT'
	));

	// Free fees
	$free_fees_price = 0;
	
	if (isset($configuration['PS_SHIPPING_FREE_PRICE'])) {
	    $free_fees_price = Tools::convertPrice((float)$configuration['PS_SHIPPING_FREE_PRICE'], Currency::getCurrencyInstance((int)$this->id_currency));
	}
	
	$orderTotalwithDiscounts = $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, null, null, false);
	
	if ($orderTotalwithDiscounts >= (float)($free_fees_price) && (float)($free_fees_price) > 0) {
	    Cache::store($cache_id, $shipping_cost);
	    return $shipping_cost;
	}

	if (isset($configuration['PS_SHIPPING_FREE_WEIGHT'])
	    && $this->getTotalWeight() >= (float)$configuration['PS_SHIPPING_FREE_WEIGHT']
	    && (float)$configuration['PS_SHIPPING_FREE_WEIGHT'] > 0) {
	    Cache::store($cache_id, $shipping_cost);
	    return $shipping_cost;
	}

	// Get shipping cost using correct method
	if ($carrier->range_behavior) {
	    // Get id zone
	    if( ! isset($id_zone))
	    {
		// Get id zone
		if (isset($this->id_address_delivery)
		    && $this->id_address_delivery
		    && Customer::customerHasAddress($this->id_customer, $this->id_address_delivery)) {
		    $id_zone = Address::getZoneById((int)$this->id_address_delivery);
		}
		else {
		    $id_zone = (int)$default_country->id_zone;
		}
	    }

	    $check_delivery_price_by_weight = Carrier::checkDeliveryPriceByWeight((int)$carrier->id, $this->getTotalWeight(), (int)$id_zone);

	    // Code Review V&V TO FINISH
	    $check_delivery_price_by_price = Carrier::checkDeliveryPriceByPrice(
		$carrier->id,
		$this->getOrderTotal(
		    true,
		    Cart::BOTH_WITHOUT_SHIPPING,
		    $product_list
		),
		$id_zone,
		(int)$this->id_currency
	    );

	    if (
		(
		    $carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT
		    && !$check_delivery_price_by_weight
		)
		||
		(
		    $carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_PRICE
		    && !$check_delivery_price_by_price
		)
	    ) {
		$shipping_cost += 0;
	    }
	    else {
		if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT) {
		    $shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight($product_list), $id_zone);
		}
		else {
		    // by price
		    $shipping_cost += $carrier->getDeliveryPriceByPrice($order_total, $id_zone, (int)$this->id_currency);
		}
	    }
	}
	else {
	    if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT) {
		$shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight($product_list), $id_zone);
	    }
	    else {
		$shipping_cost += $carrier->getDeliveryPriceByPrice($order_total, $id_zone, (int)$this->id_currency);
	    }
	}
	
	// Adding handling charges
	if (isset($configuration['PS_SHIPPING_HANDLING']) && $carrier->shipping_handling) {
	    $shipping_cost += (float)$configuration['PS_SHIPPING_HANDLING'];
	}

	// Additional Shipping Cost per product
	foreach ($products as $product) {
	    $shipping_cost += $product['additional_shipping_cost'] * $product['cart_quantity'];
	}

	$shipping_cost = Tools::convertPrice($shipping_cost, Currency::getCurrencyInstance((int)$this->id_currency));

	//get external shipping cost from module
	if ($carrier->shipping_external) {
	    $module_name = $carrier->external_module_name;
	    $module = Module::getInstanceByName($module_name);

	    if (Validate::isLoadedObject($module)) {
		if (array_key_exists('id_carrier', $module)) {
		    $module->id_carrier = $carrier->id;
		}
		
		if ($carrier->need_range) {
		    if (method_exists($module, 'getPackageShippingCost')) {
			$shipping_cost = $module->getPackageShippingCost($this, $shipping_cost, $products);
		    }
		    else {
			$shipping_cost = $module->getOrderShippingCost($this, $shipping_cost);
		    }
		}
		else {
		    $shipping_cost = $module->getOrderShippingCostExternal($this);
		}

		// Check if carrier is available
		if ($shipping_cost === false) {
		    Cache::store($cache_id, false);
		    return false;
		}
	    }
	    else {
		Cache::store($cache_id, false);
		return false;
	    }
	}

	Cache::store($cache_id, (float)Tools::ps_round((float)$shipping_cost, 2));

	return $shipping_cost;
    }
    
    public function getSummaryDetails($id_lang = null, $refresh = false)
    {
        if ( ! $id_lang) {
	    $id_lang = Context::getContext()->language->id;
        }
        
	$summary = parent::getSummaryDetails($id_lang, $refresh);
	$taxes = $this->getTaxDetails($summary['products']);

        $total_price             = $summary['total_price'];
        $total_price_without_tax = $summary['total_price_without_tax'];
        $total_tax               = $summary['total_tax'];
        $total_shipping          = $summary['total_shipping'];
        $total_shipping_tax_exc  = Order::calculateCompundTaxPrice($summary['total_shipping'], $taxes);
	$total_payment           = $this->getOrderTotal(true, Cart::ONLY_PAYMENT);
	$total_payment_tax_exc   = Order::calculateCompundTaxPrice($total_payment, $taxes);
	$payment_cost_name       = '';
	$total_shipping_plus     = 0;
        $customer                = Context::getContext()->customer;
	$isLogged                = Validate::isLoadedObject($customer);
		
	if ( ! $isLogged) {
	    $total_price -= $total_shipping;
	    $total_price_without_tax -= $total_shipping_tax_exc;
	    $total_tax -= ($total_shipping - $total_shipping_tax_exc);
	    $total_shipping = 0; 
	    $total_shipping_tax_exc = 0;
	    $total_shipping_plus = 1;
	}
		
	if ($this->id_payment) {
	    $paymentCost = new PaymentCost($this->id_payment, $id_lang);

	    if ( ! Tools::isEmpty($paymentCost->cost_name)) {
		$payment_cost_name = $paymentCost->cost_name;
	    }
	}
        
        $override = array(
            'total_price'             => $total_price,
            'total_price_without_tax' => $total_price_without_tax,
            'total_tax'               => $total_tax,
            'total_shipping'          => $total_shipping,
            'total_shipping_tax_exc'  => $total_shipping_tax_exc,
            'total_shipping_plus'     => $total_shipping_plus,
            'total_payment'           => $total_payment,
	    'total_payment_tax_exc'   => $total_payment_tax_exc,
            'payment_cost_name'       => $payment_cost_name
        );
        
        $summary['total_payment'] = $total_payment;
        $summary['total_payment_tax_exc'] = $total_payment_tax_exc;
	
	if ($taxes) {
	    $total_tax = 0;
	    
	    Order::addCompoundTaxesToTaxArray($taxes, array($total_shipping, $total_payment, $summary['total_wrapping'], -abs($summary['total_discounts'])));
    
	    foreach ($taxes as $tax_details)
	    {
		$total_tax += (float)$tax_details['total'];
	    }
	    
	    $override['total_tax'] = Tools::ps_round($total_tax, 2);
	    
	    $override['total_price_without_tax'] = (float)Tools::ps_round($override['total_price'] - $override['total_tax'], 2);
	}
	
		$override['taxes'] = $taxes;

        return array_merge($summary, $override);
    }
}


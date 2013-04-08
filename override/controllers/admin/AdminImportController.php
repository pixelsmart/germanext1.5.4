<?php
class AdminImportController extends AdminImportControllerCore
{
	public static $validators = array(
		'active'            => array('AdminImportController', 'getBoolean'),
		'tax_rate'          => array('AdminImportController', 'getPrice'),
		 /** Tax excluded */
		'price_tex'         => array('AdminImportController', 'getPrice'),
		 /** Tax included */
		'price_tin'         => array('AdminImportController', 'getPrice'),
		'base_net'          => array('AdminImportController', 'getPrice'),
		'reduction_price'   => array('AdminImportController', 'getPrice'),
		'reduction_percent' => array('AdminImportController', 'getPrice'),
		'wholesale_price'   => array('AdminImportController', 'getPrice'),
		'ecotax'            => array('AdminImportController', 'getPrice'),
		'name'              => array('AdminImportController', 'createMultiLangField'),
		'description'       => array('AdminImportController', 'createMultiLangField'),
		'description_short' => array('AdminImportController', 'createMultiLangField'),
		'meta_title'        => array('AdminImportController', 'createMultiLangField'),
		'meta_keywords'     => array('AdminImportController', 'createMultiLangField'),
		'meta_description'  => array('AdminImportController', 'createMultiLangField'),
		'link_rewrite'      => array('AdminImportController', 'createMultiLangField'),
		'available_now'     => array('AdminImportController', 'createMultiLangField'),
		'available_later'   => array('AdminImportController', 'createMultiLangField'),
		'category'          => array('AdminImportController', 'split'),
		'online_only'       => array('AdminImportController', 'getBoolean')
	);

	public function __construct()
	{
		$this->entities = array(
			$this->l('Categories'),
			$this->l('Products'),
			$this->l('Combinations'),
			$this->l('Customers'),
			$this->l('Addresses'),
			$this->l('Manufacturers'),
			$this->l('Suppliers'),
		);

		// @since 1.5.0
		if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
		{
			$this->entities = array_merge(
				$this->entities,
				array(
					$this->l('Supply Orders'),
					$this->l('Supply Orders Details'),
				)
			);
		}

		$this->entities = array_flip($this->entities);

		switch ((int)Tools::getValue('entity'))
		{
			case $this->entities[$this->l('Combinations')]:
				$this->required_fields = array(
					'id_product',
					'group',
					'attribute'
				);

				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id_product' => array('label' => $this->l('Product ID').'*'),
					'group' => array(
						'label' => $this->l('Attribute (Name:Type:Position)').'*'
					),
					'attribute' => array(
						'label' => $this->l('Value (Value:Position)').'*'
					),
					'supplier_reference' => array('label' => $this->l('Supplier reference')),
					'reference' => array('label' => $this->l('Reference')),
					'ean13' => array('label' => $this->l('EAN13')),
					'upc' => array('label' => $this->l('UPC')),
					'wholesale_price' => array('label' => $this->l('Wholesale price')),
					'price' => array('label' => $this->l('Impact on price')),
					'ecotax' => array('label' => $this->l('Ecotax')),
					'quantity' => array('label' => $this->l('Quantity')),
					'weight' => array('label' => $this->l('Impact on weight')),
					'base_net' => array('label' => $this->l('Base net')), 
					'default_on' => array('label' => $this->l('Default')),
					'image_position' => array(
						'label' => $this->l('Image position')
					),
					'image_url' => array('label' => $this->l('Image URL')),
					'delete_existing_images' => array(
						'label' => $this->l('Delete existing images (0 = No, 1 = Yes)')
					),
				);

				self::$default_values = array(
					'reference' => '',
					'supplier_reference' => '',
					'ean13' => '',
					'upc' => '',
					'wholesale_price' => 0,
					'price' => 0,
					'ecotax' => 0,
					'quantity' => 0,
					'weight' => 0,
					'base_net' => 0,
					'default_on' => 0
				);
			break;

			case $this->entities[$this->l('Categories')]:
				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'active' => array('label' => $this->l('Active (0/1)')),
					'name' => array('label' => $this->l('Name *')),
					'parent' => array('label' => $this->l('Parent category')),
					'description' => array('label' => $this->l('Description')),
					'meta_title' => array('label' => $this->l('Meta title')),
					'meta_keywords' => array('label' => $this->l('Meta keywords')),
					'meta_description' => array('label' => $this->l('Meta description')),
					'link_rewrite' => array('label' => $this->l('URL rewritten')),
					'image' => array('label' => $this->l('Image URL')),
					'shop' => array(
						'label' => $this->l('ID / Name of shop'),
						'help' => $this->l('Ignore this field if you don\'t use the Multistore tool. If you leave this field empty, the default shop will be used.'),
					),
				);

				self::$default_values = array(
					'active' => '1',
					'parent' => Configuration::get('PS_HOME_CATEGORY'),
					'link_rewrite' => ''
				);
			break;

			case $this->entities[$this->l('Products')]:
				self::$validators['image'] = array(
					'AdminImportController',
					'split'
				);

				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'active' => array('label' => $this->l('Active (0/1)')),
					'name' => array('label' => $this->l('Name *')),
					'category' => array('label' => $this->l('Categories (x,y,z...)')),
					'price_tex' => array('label' => $this->l('Price tax excluded')),
					'price_tin' => array('label' => $this->l('Price tax included')),
					'id_tax_rules_group' => array('label' => $this->l('Tax rules ID')),
					'wholesale_price' => array('label' => $this->l('Wholesale price')),
					'on_sale' => array('label' => $this->l('On sale (0/1)')),
					'reduction_price' => array('label' => $this->l('Discount amount')),
					'reduction_percent' => array('label' => $this->l('Discount percent')),
					'reduction_from' => array('label' => $this->l('Discount from (yyyy-mm-dd)')),
					'reduction_to' => array('label' => $this->l('Discount to (yyyy-mm-dd)')),
					'reference' => array('label' => $this->l('Reference #')),
					'supplier_reference' => array('label' => $this->l('Supplier reference #')),
					'supplier' => array('label' => $this->l('Supplier')),
					'manufacturer' => array('label' => $this->l('Manufacturer')),
					'ean13' => array('label' => $this->l('EAN13')),
					'upc' => array('label' => $this->l('UPC')),
					'ecotax' => array('label' => $this->l('Ecotax')),
					'weight' => array('label' => $this->l('Weight')),
					'base_net' => array('label' => $this->l('Base net')),
					'base_unit' => array('label' => $this->l('Base unit')),
					'quantity' => array('label' => $this->l('Quantity')),
					'description_short' => array('label' => $this->l('Short description')),
					'description' => array('label' => $this->l('Description')),
					'tags' => array('label' => $this->l('Tags (x,y,z...)')),
					'meta_title' => array('label' => $this->l('Meta title')),
					'meta_keywords' => array('label' => $this->l('Meta keywords')),
					'meta_description' => array('label' => $this->l('Meta description')),
					'link_rewrite' => array('label' => $this->l('URL rewritten')),
					'available_now' => array('label' => $this->l('Text when in stock')),
					'available_later' => array('label' => $this->l('Text when backorder allowed')),
					'available_for_order' => array('label' => $this->l('Available for order')),
					'date_add' => array('label' => $this->l('Product creation date')),
					'show_price' => array('label' => $this->l('Show price')),
					'image' => array('label' => $this->l('Image URLs (x,y,z...)')),
					'delete_existing_images' => array(
						'label' => $this->l('Delete existing images (0 = No, 1 = Yes)')
					),
					'features' => array('label' => $this->l('Feature(Name:Value:Position)')),
					'online_only' => array('label' => $this->l('Available online only')),
					'condition' => array('label' => $this->l('Condition')),
					'shop' => array(
						'label' => $this->l('ID / Name of shop'),
						'help' => $this->l('Ignore this field if you don\'t use the Multistore tool. If you leave this field empty, the default shop will be used.')
					),
				);

				self::$default_values = array(
					'id_category' => array((int)Configuration::get('PS_HOME_CATEGORY')),
					'id_category_default' => (int)Configuration::get('PS_HOME_CATEGORY'),
					'active' => '1',
					'quantity' => 0,
					'price' => 0,
					'id_tax_rules_group' => 0,
					'description_short' => array((int)Configuration::get('PS_LANG_DEFAULT') => ''),
					'link_rewrite' => array((int)Configuration::get('PS_LANG_DEFAULT') => ''),
					'online_only' => 0,
					'condition' => 'new',
					'shop' => Configuration::get('PS_SHOP_DEFAULT'),
					'date_add' => date('Y-m-d H:i:s'),
					'condition' => 'new',
				);
			break;

			case $this->entities[$this->l('Customers')]:
				//Overwrite required_fields AS only email is required whereas other entities
				$this->required_fields = array('email', 'passwd', 'lastname', 'firstname');

				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'active' => array('label' => $this->l('Active  (0/1)')),
					'id_gender' => array('label' => $this->l('Gender ID (Mr = 1, Ms = 2, else 0)')),
					'email' => array('label' => $this->l('Email *')),
					'passwd' => array('label' => $this->l('Password *')),
					'birthday' => array('label' => $this->l('Birthday (yyyy-mm-dd)')),
					'lastname' => array('label' => $this->l('Last Name *')),
					'firstname' => array('label' => $this->l('First Name *')),
					'newsletter' => array('label' => $this->l('Newsletter (0/1)')),
					'optin' => array('label' => $this->l('Opt-in (0/1)')),
					'id_shop' => array(
						'label' => $this->l('ID / Name of shop'),
						'help' => $this->l('Ignore this field if you don\'t use the Multistore tool. If you leave this field empty, the default shop will be used.')
					),
				);

				self::$default_values = array(
					'active' => '1',
					'id_shop' => Configuration::get('PS_SHOP_DEFAULT'),
				);
			break;

			case $this->entities[$this->l('Addresses')]:
				//Overwrite required_fields
				$this->required_fields = array(
					'lastname',
					'firstname',
					'address1',
					'postcode',
					'country',
					'city'
				);

				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'alias' => array('label' => $this->l('Alias *')),
					'active' => array('label' => $this->l('Active  (0/1)')),
					'customer_email' => array('label' => $this->l('Customer email')),
					'id_customer' => array('label' => $this->l('Customer ID:')),
					'manufacturer' => array('label' => $this->l('Manufacturer')),
					'supplier' => array('label' => $this->l('Supplier')),
					'company' => array('label' => $this->l('Company')),
					'lastname' => array('label' => $this->l('Last Name *')),
					'firstname' => array('label' => $this->l('First Name *')),
					'address1' => array('label' => $this->l('Address 1 *')),
					'address2' => array('label' => $this->l('Address 2')),
					'postcode' => array('label' => $this->l('Postal code / Zipcode*')),
					'city' => array('label' => $this->l('City *')),
					'country' => array('label' => $this->l('Country *')),
					'state' => array('label' => $this->l('State')),
					'other' => array('label' => $this->l('Other')),
					'phone' => array('label' => $this->l('Phone')),
					'phone_mobile' => array('label' => $this->l('Mobile Phone')),
					'vat_number' => array('label' => $this->l('VAT number')),
				);

				self::$default_values = array(
					'alias' => 'Alias',
					'postcode' => 'X'
				);
			break;

			case $this->entities[$this->l('Manufacturers')]:
			case $this->entities[$this->l('Suppliers')]:
				//Overwrite validators AS name is not MultiLangField
				self::$validators = array(
					'description' => array('AdminImportController', 'createMultiLangField'),
					'short_description' => array('AdminImportController', 'createMultiLangField'),
					'meta_title' => array('AdminImportController', 'createMultiLangField'),
					'meta_keywords' => array('AdminImportController', 'createMultiLangField'),
					'meta_description' => array('AdminImportController', 'createMultiLangField'),
				);

				$this->available_fields = array(
					'no' => array('label' => $this->l('Ignore this column')),
					'id' => array('label' => $this->l('ID')),
					'active' => array('label' => $this->l('Active (0/1)')),
					'name' => array('label' => $this->l('Name *')),
					'description' => array('label' => $this->l('Description')),
					'short_description' => array('label' => $this->l('Short description')),
					'meta_title' => array('label' => $this->l('Meta title')),
					'meta_keywords' => array('label' => $this->l('Meta keywords')),
					'meta_description' => array('label' => $this->l('Meta description')),
					'shop' => array(
						'label' => $this->l('ID / Name of group shop'),
						'help' => $this->l('Ignore this field if you don\'t use the Multistore tool. If you leave this field empty, the default shop will be used.'),
					),
				);

				self::$default_values = array(
					'shop' => Shop::getGroupFromShop(Configuration::get('PS_SHOP_DEFAULT')),
				);
			break;
			// @since 1.5.0
			case $this->entities[$this->l('Supply Orders')]:
				if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
				{
					// required fields
					$this->required_fields = array(
						'id_supplier',
						'id_warehouse',
						'reference',
						'date_delivery_expected',
					);
					// available fields
					$this->available_fields = array(
						'no' => array('label' => $this->l('Ignore this column')),
						'id' => array('label' => $this->l('ID')),
						'id_supplier' => array('label' => $this->l('Supplier ID *')),
						'id_lang' => array('label' => $this->l('Lang ID')),
						'id_warehouse' => array('label' => $this->l('Warehouse ID *')),
						'id_currency' => array('label' => $this->l('Currency ID *')),
						'reference' => array('label' => $this->l('Supply Order Reference *')),
						'date_delivery_expected' => array('label' => $this->l('Delivery Date (Y-M-D)*')),
						'discount_rate' => array('label' => $this->l('Discount Rate')),
						'is_template' => array('label' => $this->l('Template')),
					);
					// default values
					self::$default_values = array(
						'id_lang' => (int)Configuration::get('PS_LANG_DEFAULT'),
						'id_currency' => Currency::getDefaultCurrency()->id,
						'discount_rate' => '0',
						'is_template' => '0',
					);
				}
			break;
			// @since 1.5.0
			case $this->entities[$this->l('Supply Orders Details')]:
				if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
				{
					// required fields
					$this->required_fields = array(
						'supply_order_reference',
						'id_product',
						'unit_price_te',
						'quantity_expected',
					);
					// available fields
					$this->available_fields = array(
						'no' => array('label' => $this->l('Ignore this column')),
						'supply_order_reference' => array('label' => $this->l('Supply Order Reference *')),
						'id_product' => array('label' => $this->l('Product ID *')),
						'id_product_attribute' => array('label' => $this->l('Product Attribute ID')),
						'unit_price_te' => array('label' => $this->l('Unit Price (tax excl.) *')),
						'quantity_expected' => array('label' => $this->l('Quantity Expected *')),
						'discount_rate' => array('label' => $this->l('Discount Rate')),
						'tax_rate' => array('label' => $this->l('Tax Rate')),
					);
					// default values
					self::$default_values = array(
						'discount_rate' => '0',
						'tax_rate' => '0',
					);
				}
		}
		
		$this->separator = strval(trim(Tools::getValue('separator', ';')));

		if (is_null(Tools::getValue('multiple_value_separator')) || trim(Tools::getValue('multiple_value_separator')) == '') {
			$this->multiple_value_separator = ',';
		}
		else {
			$this->multiple_value_separator = Tools::getValue('multiple_value_separator');
		}

		AdminController::__construct();
	}
}


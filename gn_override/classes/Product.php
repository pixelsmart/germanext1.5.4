<?php
class Product extends ProductCore
{
	public $unit_net;
	
	public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null) {
		if ( ! array_key_exists('unit_net', self::$definition['fields'])) {
			self::$definition['fields']['unit_net'] = array('type' => parent::TYPE_STRING, 'validate' => 'isString');
		}
		
		parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
	}
	
	public function getFields() {
		parent::validateFields();

		$fields['unit_net'] = pSQL($this->unit_net);
        
		return parent::getFields();
	}

	public function updateAttribute($id_product_attribute, $wholesale_price, $price, $weight, $unit, $ecotax, $id_images, $reference, $ean13, $default, $location = null, $upc = null, $minimal_quantity = null, $available_date = null, $update_all_fields = true, array $id_shop_list = array(), $net = 0) {
		$combination = new Combination($id_product_attribute);

		if ( ! $update_all_fields) {
			$combination->setFieldsToUpdate(array(
				'price'             => ! is_null($price),
				'wholesale_price'   => ! is_null($wholesale_price),
				'ecotax'            => ! is_null($ecotax),
				'weight'            => ! is_null($weight),
				'unit_price_impact' => ! is_null($unit),
				'unit_net_impact'   => ! is_null($net),
				'default_on'        => ! is_null($ecotax),
				'minimal_quantity'  => ! is_null($minimal_quantity),
				'available_date'    => ! is_null($available_date),
			));
		}

		$price = str_replace(',', '.', $price);
		$weight = str_replace(',', '.', $weight);

		$combination->price = (float)$price;
		$combination->wholesale_price = (float)$wholesale_price;
		$combination->ecotax = (float)$ecotax;
		$combination->weight = (float)$weight;
		$combination->unit_price_impact = (float)$unit;
		$combination->unit_net_impact = (float)$net;
		$combination->reference = pSQL($reference);
		$combination->location = pSQL($location);
		$combination->ean13 = pSQL($ean13);
		$combination->upc = pSQL($upc);
		$combination->default_on = (int)$default;
		$combination->minimal_quantity = (int)$minimal_quantity;
		$combination->available_date = $available_date ? pSQL($available_date) : '0000-00-00';
		
		if (count($id_shop_list)) {
			$combination->id_shop_list = $id_shop_list;
		}

		$combination->save();

		if ( ! empty($id_images)) {
			$combination->setImages($id_images);
		}

		Product::updateDefaultAttribute($this->id);

		Hook::exec('actionProductAttributeUpdate', array('id_product_attribute' => $id_product_attribute));

		return true;
	}
    
	public function addCombinationEntity($wholesale_price, $price, $weight, $unit_impact, $ecotax, $quantity, $id_images, $reference, $id_supplier, $ean13, $default, $location = null, $upc = null, $minimal_quantity = 1, array $id_shop_list = array(), $net_impact = 0) {
		$id_product_attribute = $this->addAttribute(
			$price,
			$weight,
			$unit_impact,
			$ecotax,
			$id_images,
			$reference,
			$ean13,
			$default,
			$location,
			$upc,
			$minimal_quantity,
			$id_shop_list,
			$net_impact
		);

		$this->addSupplierReference($id_supplier, $id_product_attribute);
		$result = ObjectModel::updateMultishopTable('Combination', array(
			'wholesale_price' => (float)$wholesale_price,
		), 'a.id_product_attribute = '.(int)$id_product_attribute);

		if ( ! $id_product_attribute || ! $result) {
			return false;
		}

		if ($this->getType() == Product::PTYPE_VIRTUAL) {
			StockAvailable::setProductOutOfStock((int)$this->id, 1, null, $id_product_attribute);
		}
		else {
			StockAvailable::setProductOutOfStock((int)$this->id, StockAvailable::outOfStock($this->id), null, $id_product_attribute);
		}
		
		return $id_product_attribute;
	}
    
	public function addAttribute($price, $weight, $unit_impact, $ecotax, $id_images, $reference, $ean13, $default, $location = null, $upc = null, $minimal_quantity = 1, array $id_shop_list = array(), $net_impact = 0) {
		if ( ! $this->id) {
			return;
		}

		$price = str_replace(',', '.', $price);
		$weight = str_replace(',', '.', $weight);

		$combination = new Combination();
		$combination->id_product = (int)$this->id;
		$combination->price = (float)$price;
		$combination->ecotax = (float)$ecotax;
		$combination->quantity = 0;
		$combination->weight = (float)$weight;
		$combination->unit_price_impact = (float)$unit_impact;
		$combination->unit_net_impact = (float)$net_impact;
		$combination->reference = pSQL($reference);
		$combination->location = pSQL($location);
		$combination->ean13 = pSQL($ean13);
		$combination->upc = pSQL($upc);
		$combination->default_on = (int)$default;
		$combination->minimal_quantity = (int)$minimal_quantity;

		// if we add a combination for this shop and this product does not use the combination feature in other shop,
		// we clone the default combination in every shop linked to this product
		if ($default && !$this->hasAttributesInOtherShops()) {
			$id_shop_list_array = Product::getShopsByProduct($this->id);
			
			foreach ($id_shop_list_array as $array_shop) {
				$id_shop_list[] = $array_shop['id_shop'];
			}
		}

		if (count($id_shop_list)) {
			$combination->id_shop_list = $id_shop_list;
		}
			
		$combination->add();

		if ( ! $combination->id) {
			return false;
		}

		Product::updateDefaultAttribute($this->id);

		if ( ! empty($id_images)) {
			$combination->setImages($id_images);
		}

		return (int)$combination->id;
	}
	
	public function getAttributesGroups($id_lang) {
		if ( ! Combination::isFeatureActive()) {
			return array();
		}
		
		$sql = '
		SELECT 
			ag.`id_attribute_group`, 
			ag.`is_color_group`, 
			agl.`name` AS group_name, 
			agl.`public_name` AS public_group_name,
			a.`id_attribute`, 
			al.`name` AS attribute_name, 
			a.`color` AS attribute_color, 
			pa.`id_product_attribute`,
			IFNULL(stock.quantity, 0) as quantity, 
			product_attribute_shop.`price`, 
			product_attribute_shop.`ecotax`, 
			pa.`weight`,
			product_attribute_shop.`default_on`, 
			pa.`reference`, 
			product_attribute_shop.`unit_price_impact`, 
			product_attribute_shop.`unit_net_impact`,
			pa.`minimal_quantity`, 
			pa.`available_date`, 
			ag.`group_type`
		FROM 
			`'._DB_PREFIX_.'product_attribute` pa
			'.Shop::addSqlAssociation('product_attribute', 'pa').'
			'.Product::sqlStock('pa', 'pa').'
			LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
			LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON a.`id_attribute` = al.`id_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON ag.`id_attribute_group` = agl.`id_attribute_group`
			'.Shop::addSqlAssociation('attribute', 'a').'
		WHERE 
			pa.`id_product` = '.(int)$this->id.'
			AND 
			al.`id_lang` = '.(int)$id_lang.'
			AND 
			agl.`id_lang` = '.(int)$id_lang.'
		GROUP BY 
			id_attribute_group, id_product_attribute
		ORDER BY 
			ag.`position` ASC, a.`position` ASC';
			
		return Db::getInstance()->executeS($sql);
	}
}

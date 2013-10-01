<?php
class ProductController extends ProductControllerCore
{
	public function initContent() {
		parent::initContent();

		$avNowOrig   = trim($this->product->available_now);
		$avLaterOrig = trim($this->product->available_later);
		$id_lang     = (int)$this->context->cookie->id_lang;
		
		if ($avNowOrig == '') {
			$this->product->available_now = Configuration::get('GN_AVAILABLE_NOW', $id_lang);
		}
		
		if ($avLaterOrig == '') {
			$this->product->available_later = Configuration::get('GN_AVAILABLE_LATER', $id_lang);
		}
		
		if ($this->product->id_base_unit) {
			$this->product->unity = Germanext::getBaseUnitById($this->product->id_base_unit, $id_lang);
		}
	}
	
	protected function assignAttributesGroups() {
		$colors = array();
		$groups = array();

		// @todo (RM) should only get groups and not all declination ?
		$attributes_groups = $this->product->getAttributesGroups($this->context->language->id);
		
		if (is_array($attributes_groups) && $attributes_groups) {
			$combination_images = $this->product->getCombinationImages($this->context->language->id);
			$combination_prices_set = array();
			
			foreach ($attributes_groups as $k => $row) {
				// Color management
				if ((isset($row['attribute_color']) && $row['attribute_color']) || (file_exists(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))) {
					$colors[$row['id_attribute']]['value'] = $row['attribute_color'];
					$colors[$row['id_attribute']]['name'] = $row['attribute_name'];
					if (!isset($colors[$row['id_attribute']]['attributes_quantity']))
						$colors[$row['id_attribute']]['attributes_quantity'] = 0;
					$colors[$row['id_attribute']]['attributes_quantity'] += (int)$row['quantity'];
				}
				
				if (!isset($groups[$row['id_attribute_group']]))
					$groups[$row['id_attribute_group']] = array(
						'name' => $row['public_group_name'],
						'group_type' => $row['group_type'],
						'default' => -1,
					);

				$groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];
				if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1)
					$groups[$row['id_attribute_group']]['default'] = (int)$row['id_attribute'];
				if (!isset($groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']]))
					$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] = 0;
				$groups[$row['id_attribute_group']]['attributes_quantity'][$row['id_attribute']] += (int)$row['quantity'];

				if ($row['available_date'] != '0000-00-00 00:00:00' && $row['available_date'] != '0000-00-00')
					$available_date = Tools::displayDate($row['available_date'], $this->context->language->id);
				else
					$available_date = $row['available_date'];

				$combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
				$combinations[$row['id_product_attribute']]['attributes'][] = (int)$row['id_attribute'];
				$combinations[$row['id_product_attribute']]['price'] = (float)$row['price'];

				// Call getPriceStatic in order to set $combination_specific_price
				if (!isset($combination_prices_set[(int)$row['id_product_attribute']]))
				{
					Product::getPriceStatic((int)$this->product->id, false, $row['id_product_attribute'], 6, null, false, true, 1, false, null, null, null, $combination_specific_price);
					$combination_prices_set[(int)$row['id_product_attribute']] = true;
					$combinations[$row['id_product_attribute']]['specific_price'] = $combination_specific_price;
				}
				$combinations[$row['id_product_attribute']]['ecotax'] = (float)$row['ecotax'];
				$combinations[$row['id_product_attribute']]['weight'] = (float)$row['weight'];
				$combinations[$row['id_product_attribute']]['quantity'] = (int)$row['quantity'];
				$combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
				$combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
				$combinations[$row['id_product_attribute']]['net_impact'] = $row['unit_net_impact'];
				$combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
				$combinations[$row['id_product_attribute']]['unit_net'] = (float)$this->product->unit_net + (float)$row['unit_net_impact'];
				$combinations[$row['id_product_attribute']]['available_date'] = $available_date;

				if (isset($combination_images[$row['id_product_attribute']][0]['id_image']))
					$combinations[$row['id_product_attribute']]['id_image'] = $combination_images[$row['id_product_attribute']][0]['id_image'];
				else
					$combinations[$row['id_product_attribute']]['id_image'] = -1;
			}
			// wash attributes list (if some attributes are unavailables and if allowed to wash it)
			if (!Product::isAvailableWhenOutOfStock($this->product->out_of_stock) && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0)
			{
				foreach ($groups as &$group)
					foreach ($group['attributes_quantity'] as $key => &$quantity)
						if (!$quantity)
							unset($group['attributes'][$key]);

				foreach ($colors as $key => $color)
					if (!$color['attributes_quantity'])
						unset($colors[$key]);
			}
			foreach ($combinations as $id_product_attribute => $comb)
			{
				$attribute_list = '';
				foreach ($comb['attributes'] as $id_attribute)
					$attribute_list .= '\''.(int)$id_attribute.'\',';
				$attribute_list = rtrim($attribute_list, ',');
				$combinations[$id_product_attribute]['list'] = $attribute_list;
			}
			$this->context->smarty->assign(array(
				'groups' => $groups,
				'combinations' => $combinations,
				'colors' => (count($colors)) ? $colors : false,
				'combinationImages' => $combination_images));
		}
	}
}

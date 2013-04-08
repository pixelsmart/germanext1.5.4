<?php
class AdminProductsController extends AdminProductsControllerCore
{
    protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);
        
        if (Tools::getIsset('unit_net') != null)
        {
			$object->unit_net = str_replace(',', '.', Tools::getValue('unit_net'));
        }
    }
    
	public function renderListAttributes($product, $currency)
	{
		$this->bulk_actions = array('delete' => array('text' => $this->l('Delete selected'), 'confirm' => $this->l('Delete selected items?')));
		$this->addRowAction('edit');
		$this->addRowAction('default');
		$this->addRowAction('delete');

		$color_by_default = '#BDE5F8';

		$this->fields_list = array(
			'attributes' => array('title' => $this->l('Attributes'), 'align' => 'left'),
			'price' => array('title' => $this->l('Impact'), 'type' => 'price', 'align' => 'left', 'width' => 70),
			'weight' => array('title' => $this->l('Weight'), 'align' => 'left', 'width' => 70),
			'reference' => array('title' => $this->l('Reference'), 'align' => 'left', 'width' => 70),
			'ean13' => array('title' => $this->l('EAN13'), 'align' => 'left', 'width' => 70),
			'upc' => array('title' => $this->l('UPC'), 'align' => 'left', 'width' => 70)
		);

		if ($product->id)
		{
			/* Build attributes combinations */
			$combinations = $product->getAttributeCombinations($this->context->language->id);
			$groups = array();
			$comb_array = array();
			if (is_array($combinations))
			{
				$combination_images = $product->getCombinationImages($this->context->language->id);
				foreach ($combinations as $k => $combination)
				{
					$price = Tools::displayPrice($combination['price'], $currency);

					$comb_array[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
					$comb_array[$combination['id_product_attribute']]['attributes'][] = array($combination['group_name'], $combination['attribute_name'], $combination['id_attribute']);
					$comb_array[$combination['id_product_attribute']]['wholesale_price'] = $combination['wholesale_price'];
					$comb_array[$combination['id_product_attribute']]['price'] = $price;
					$comb_array[$combination['id_product_attribute']]['weight'] = $combination['weight'].Configuration::get('PS_WEIGHT_UNIT');
					$comb_array[$combination['id_product_attribute']]['unit_impact'] = $combination['unit_price_impact'];
					$comb_array[$combination['id_product_attribute']]['net_impact'] = $combination['unit_net_impact'];
					$comb_array[$combination['id_product_attribute']]['reference'] = $combination['reference'];
					$comb_array[$combination['id_product_attribute']]['ean13'] = $combination['ean13'];
					$comb_array[$combination['id_product_attribute']]['upc'] = $combination['upc'];
					$comb_array[$combination['id_product_attribute']]['id_image'] = isset($combination_images[$combination['id_product_attribute']][0]['id_image']) ? $combination_images[$combination['id_product_attribute']][0]['id_image'] : 0;
					$comb_array[$combination['id_product_attribute']]['available_date'] = strftime($combination['available_date']);
					$comb_array[$combination['id_product_attribute']]['default_on'] = $combination['default_on'];
					if ($combination['is_color_group'])
						$groups[$combination['id_attribute_group']] = $combination['group_name'];
				}
			}

			$irow = 0;
			if (isset($comb_array))
			{
				foreach ($comb_array as $id_product_attribute => $product_attribute)
				{
					$list = '';

					/* In order to keep the same attributes order */
					asort($product_attribute['attributes']);

					foreach ($product_attribute['attributes'] as $attribute)
						$list .= htmlspecialchars($attribute[0]).' - '.htmlspecialchars($attribute[1]).', ';

					$list = rtrim($list, ', ');
					$comb_array[$id_product_attribute]['image'] = $product_attribute['id_image'] ? new Image($product_attribute['id_image']) : false;
					$comb_array[$id_product_attribute]['available_date'] = $product_attribute['available_date'] != 0 ? date('Y-m-d', strtotime($product_attribute['available_date'])) : '0000-00-00';
					$comb_array[$id_product_attribute]['attributes'] = $list;

					if ($product_attribute['default_on'])
					{
						$comb_array[$id_product_attribute]['name'] = 'is_default';
						$comb_array[$id_product_attribute]['color'] = $color_by_default;
					}
				}
			}
		}

		foreach ($this->actions_available as $action)
		{
			if (!in_array($action, $this->actions) && isset($this->$action) && $this->$action)
				$this->actions[] = $action;
		}

		$helper = new HelperList();
		$helper->identifier = 'id_product_attribute';
		$helper->token = $this->token;
		$helper->currentIndex = self::$currentIndex;
		$helper->no_link = true;
		$helper->simple_header = true;
		$helper->show_toolbar = false;
		$helper->shopLinkType = $this->shopLinkType;
		$helper->actions = $this->actions;
		$helper->list_skip_actions = $this->list_skip_actions;
		$helper->colorOnBackground = true;
		$helper->override_folder = $this->tpl_folder.'combination/';

		return $helper->generateList($comb_array, $this->fields_list);
	}
	
	public function initFormPrices($obj)
	{
		$data = $this->createTemplate($this->tpl_form);
		$product = $obj;
		if ($obj->id)
		{
			$shops = Shop::getShops();
			$countries = Country::getCountries($this->context->language->id);
			$groups = Group::getGroups($this->context->language->id);
			$currencies = Currency::getCurrencies();
			$attributes = $obj->getAttributesGroups((int)$this->context->language->id);
			$combinations = array();
			foreach ($attributes as $attribute)
			{
				$combinations[$attribute['id_product_attribute']]['id_product_attribute'] = $attribute['id_product_attribute'];
				if (!isset($combinations[$attribute['id_product_attribute']]['attributes']))
					$combinations[$attribute['id_product_attribute']]['attributes'] = '';
				$combinations[$attribute['id_product_attribute']]['attributes'] .= $attribute['attribute_name'].' - ';

				$combinations[$attribute['id_product_attribute']]['price'] = Tools::displayPrice(
					Tools::convertPrice(
						Product::getPriceStatic((int)$obj->id, false, $attribute['id_product_attribute']),
						$this->context->currency
					), $this->context->currency
				);
			}
			foreach ($combinations as &$combination)
				$combination['attributes'] = rtrim($combination['attributes'], ' - ');
			$data->assign('specificPriceModificationForm', $this->_displaySpecificPriceModificationForm(
				$this->context->currency, $shops, $currencies, $countries, $groups)
			);

			$data->assign(array(
				'shops' => $shops,
				'admin_one_shop' => count($this->context->employee->getAssociatedShops()) == 1,
				'currencies' => $currencies,
				'countries' => $countries,
				'groups' => $groups,
				'combinations' => $combinations,
				'product' => $product,
				'multi_shop' => Shop::isFeatureActive(),
				'link' => new Link()
			));
		}
		else
			$this->displayWarning($this->l('You must save this product before adding specific prices'));
		// prices part
		$data->assign(array(
			'link' => $this->context->link,
			'currency' => $currency = $this->context->currency,
			'tax_rules_groups' => TaxRulesGroup::getTaxRulesGroups(true),
			'taxesRatesByGroup' => TaxRulesGroup::getAssociatedTaxRatesByIdCountry($this->context->country->id),
			'ecotaxTaxRate' => Tax::getProductEcotaxRate(),
			'tax_exclude_taxe_option' => Tax::excludeTaxeOption(),
			'ps_use_ecotax' => Configuration::get('PS_USE_ECOTAX'),
			'ecotax_tax_excl' => 0,
			'unities' => Germanext::getBaseUnits()
		));
		$product_price = Tools::convertPrice($product->price, $this->context->currency, true, $this->context);
		if ($product->unit_price_ratio != 0)
			$data->assign('unit_price', Tools::ps_round($product->price / $product->unit_price_ratio, 2));
		else
			$data->assign('unit_price', 0);
		$data->assign('ps_tax', Configuration::get('PS_TAX'));

		$data->assign('country_display_tax_label', $this->context->country->display_tax_label);
		$data->assign(array(
			'currency', $this->context->currency,
			'product' => $product,
			'token' => $this->token
		));

		$this->tpl_form_vars['custom_form'] = $data->fetch();
	}
    
	public function initFormAttributes($product)
	{
		if (!Combination::isFeatureActive())
		{
			$this->displayWarning($this->l('This feature has been disabled, you can active this feature at this page:').
				' <a href="index.php?tab=AdminPerformance&token='.Tools::getAdminTokenLite('AdminPerformance').'#featuresDetachables">'.$this->l('Performances').'</a>');
			return;
		}

		$data = $this->createTemplate($this->tpl_form);

		if (Validate::isLoadedObject($product))
		{
			if ($product->is_virtual)
			{
				$data->assign('product', $product);
				$this->displayWarning($this->l('A virtual product cannot have combinations.'));
			}
			else
			{
				$attribute_js = array();
				$attributes = Attribute::getAttributes($this->context->language->id, true);
				foreach ($attributes as $k => $attribute)
					$attribute_js[$attribute['id_attribute_group']][$attribute['id_attribute']] = $attribute['name'];
				$currency = $this->context->currency;
				$data->assign('attributeJs', $attribute_js);
				$data->assign('attributes_groups', AttributeGroup::getAttributesGroups($this->context->language->id));

				$data->assign('currency', $currency);

				$images = Image::getImages($this->context->language->id, $product->id);

				$data->assign('tax_exclude_option', Tax::excludeTaxeOption());
				$data->assign('ps_weight_unit', Configuration::get('PS_WEIGHT_UNIT'));

				$data->assign('ps_use_ecotax', Configuration::get('PS_USE_ECOTAX'));
				$data->assign('field_value_unity', $this->getFieldValue($product, 'unity'));

				$data->assign('reasons', $reasons = StockMvtReason::getStockMvtReasons($this->context->language->id));
				$data->assign('ps_stock_mvt_reason_default', $ps_stock_mvt_reason_default = Configuration::get('PS_STOCK_MVT_REASON_DEFAULT'));
				$data->assign('minimal_quantity', $this->getFieldValue($product, 'minimal_quantity') ? $this->getFieldValue($product, 'minimal_quantity') : 1);
				$data->assign('available_date', ($this->getFieldValue($product, 'available_date') != 0) ? stripslashes(htmlentities(Tools::displayDate($this->getFieldValue($product, 'available_date'), $this->context->language->id))) : '0000-00-00');

				$i = 0;
				$data->assign('imageType', ImageType::getByNameNType('small', 'products'));
				$data->assign('imageWidth', (isset($image_type['width']) ? (int)($image_type['width']) : 64) + 25);
				foreach ($images as $k => $image)
				{
					$images[$k]['obj'] = new Image($image['id_image']);
					++$i;
				}
				$data->assign('images', $images);

				$data->assign($this->tpl_form_vars);
				$data->assign(array(
					'list' => $this->renderListAttributes($product, $currency),
					'product' => $product,
					'id_category' => $product->getDefaultCategory(),
					'token_generator' => Tools::getAdminTokenLite('AdminAttributeGenerator'),
					'combination_exists' => (Shop::isFeatureActive() && (Shop::getContextShopGroup()->share_stock) && count(AttributeGroup::getAttributesGroups($this->context->language->id)) > 0)
				));
			}
		}
		else
		{
			$data->assign('product', $product);
			$this->displayWarning($this->l('You must save this product before adding combinations.'));
		}

		$this->tpl_form_vars['custom_form'] = $data->fetch();
	}
    
	public function processProductAttribute()
	{
		if ( ! Combination::isFeatureActive() || !Tools::getIsset('attribute'))
        {
			return;
        }

		if (Validate::isLoadedObject($product = $this->object))
		{
			if ($this->isProductFieldUpdated('attribute_price')
            && (!Tools::getIsset('attribute_price')
            || Tools::getIsset('attribute_price') == null))
            {
				$this->errors[] = Tools::displayError('Attribute price required.');
            }
            
			if ( ! Tools::getIsset('attribute_combination_list')
            || Tools::isEmpty(Tools::getValue('attribute_combination_list')))
            {
				$this->errors[] = Tools::displayError('You must add at least one attribute.');
            }

			if ( ! count($this->errors))
			{
				if ( ! isset($_POST['attribute_wholesale_price']))
                {
                    $_POST['attribute_wholesale_price'] = 0;
                }
                
				if ( ! isset($_POST['attribute_price_impact']))
                {
                    $_POST['attribute_price_impact'] = 0;
                }
                
				if ( ! isset($_POST['attribute_weight_impact']))
                {
                    $_POST['attribute_weight_impact'] = 0;
                }
                
				if ( ! isset($_POST['attribute_ecotax']))
                {
                    $_POST['attribute_ecotax'] = 0;
                }
                
				if (Tools::getValue('attribute_default'))
                {
					$product->deleteDefaultAttributes();
                }
                
				// Change existing one
				if ($id_product_attribute = (int)Tools::getValue('id_product_attribute'))
				{
					if ($this->tabAccess['edit'] === '1')
					{
						if ($product->productAttributeExists(Tools::getValue('attribute_combination_list'), (int)$id_product_attribute))
                        {
							$this->errors[] = Tools::displayError('This attribute already exists.');
                        }
						else
						{
							if ($this->isProductFieldUpdated('available_date_attribute')
                                && ! Validate::isDateFormat(Tools::getValue('available_date_attribute')))
                            {
								$this->errors[] = Tools::displayError('Invalid date format.');
                            }
							else
							{
								$product->updateAttribute((int)$id_product_attribute,
									$this->isProductFieldUpdated('attribute_wholesale_price') ? Tools::getValue('attribute_wholesale_price') : null,
									$this->isProductFieldUpdated('attribute_price_impact') ? Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact') : null,
									$this->isProductFieldUpdated('attribute_weight_impact') ? Tools::getValue('attribute_weight') * Tools::getValue('attribute_weight_impact') : null,
									$this->isProductFieldUpdated('attribute_unit_impact') ? Tools::getValue('attribute_unity') * Tools::getValue('attribute_unit_impact') : null,
									$this->isProductFieldUpdated('attribute_ecotax') ? Tools::getValue('attribute_ecotax') : null,
									Tools::getValue('id_image_attr'),
									Tools::getValue('attribute_reference'),
									Tools::getValue('attribute_ean13'),
									$this->isProductFieldUpdated('attribute_default') ? Tools::getValue('attribute_default') : null,
									Tools::getValue('attribute_location'),
									Tools::getValue('attribute_upc'),
									$this->isProductFieldUpdated('attribute_minimal_quantity') ? Tools::getValue('attribute_minimal_quantity') : null,
									$this->isProductFieldUpdated('available_date_attribute') ? Tools::getValue('available_date_attribute') : null,
									false,
                                    array(),
                                    Tools::getValue('attribute_net') * Tools::getValue('attribute_net_impact')
                                );
							}
						}
					}
					else
						$this->errors[] = Tools::displayError('You do not have permission to add here.');
				}
				// Add new
				else
				{
					if ($this->tabAccess['add'] === '1')
					{
						if ($product->productAttributeExists(Tools::getValue('attribute_combination_list')))
							$this->errors[] = Tools::displayError('This combination already exists.');
						else
							$id_product_attribute = $product->addCombinationEntity(
								Tools::getValue('attribute_wholesale_price'),
								Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact'),
								Tools::getValue('attribute_weight') * Tools::getValue('attribute_weight_impact'),
								Tools::getValue('attribute_unity') * Tools::getValue('attribute_unit_impact'),
								Tools::getValue('attribute_ecotax'),
								0,
								Tools::getValue('id_image_attr'),
								Tools::getValue('attribute_reference'),
								null,
								Tools::getValue('attribute_ean13'),
								Tools::getValue('attribute_default'),
								Tools::getValue('attribute_location'),
								Tools::getValue('attribute_upc'),
								Tools::getValue('attribute_minimal_quantity'),
                                array(),
                                Tools::getValue('attribute_net') * Tools::getValue('attribute_net_impact')
							);
					}
					else
						$this->errors[] = Tools::displayError('You do not have permission to').'<hr>'.Tools::displayError('edit here.');
				}
				if (!count($this->errors))
				{
					$combination = new Combination((int)$id_product_attribute);
					$combination->setAttributes(Tools::getValue('attribute_combination_list'));
					$product->checkDefaultAttributes();
					
					if (Tools::getValue('attribute_default'))
					{
						Product::updateDefaultAttribute((int)$product->id);
						if(isset($id_product_attribute))
							$product->cache_default_attribute = (int)$id_product_attribute;
						if ($available_date = Tools::getValue('available_date_attribute'))
							$product->setAvailableDate($available_date);
					}
				}
			}
		}
	}
}
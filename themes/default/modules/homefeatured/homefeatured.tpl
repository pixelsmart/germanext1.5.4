{*
* 2007-2012 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA 
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- MODULE Home Featured Products -->
<div id="featured-products_block_center" class="block products_block clearfix">
	<h4>{l s='Featured products' mod='germanext'}</h4>
	{if isset($products) AND $products}
		<div class="block_content">
			{assign var='liHeight' value=342}
			{assign var='nbItemsPerLine' value=4}
			{assign var='nbLi' value=$products|@count}
			{math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
			{math equation="nbLines*liHeight" nbLines=$nbLines|ceil liHeight=$liHeight assign=ulHeight}
			<ul style="height:{$ulHeight}px;">
			{foreach from=$products item=product name=homeFeaturedProducts}
				<li class="ajax_block_product {if $smarty.foreach.homeFeaturedProducts.first}first_item{elseif $smarty.foreach.homeFeaturedProducts.last}last_item{else}item{/if} {if $smarty.foreach.homeFeaturedProducts.iteration%$nbItemsPerLine == 0}last_item_of_line{elseif $smarty.foreach.homeFeaturedProducts.iteration%$nbItemsPerLine == 1} {/if} {if $smarty.foreach.homeFeaturedProducts.iteration > ($smarty.foreach.homeFeaturedProducts.total - ($smarty.foreach.homeFeaturedProducts.total % $nbItemsPerLine))}last_line{/if}">
					<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')}" height="{$homeSize.height}" width="{$homeSize.width}" alt="{$product.name|escape:html:'UTF-8'}" />{if isset($product.new) && $product.new == 1}<span class="new">{l s='New' mod='germanext'}</span>{/if}</a>
					<h5><a href="{$product.link}" title="{$product.name|truncate:50:'...'|escape:'htmlall':'UTF-8'}">{$product.name|truncate:35:'...'|escape:'htmlall':'UTF-8'}</a></h5>

					<div class="product_desc"><a href="{$product.link}" title="{l s='More' mod='homefeatured'}">{$product.description_short|strip_tags|truncate:65:'...'}</a></div>

					<div class="gn_price">
						{if $product.show_price && ! isset($restricted_country_mode) && ! $PS_CATALOG_MODE}
							{if ! $priceDisplay}
							<p class="price_container">
								<span class="price">
								{if $product.id_product_attribute != 0 && isset($product.total_combinations) && $product.total_combinations > 0}
									<span class="gn_adds gn_pricefrom">{l s='From' mod='germanext'}</span>
								{/if} 
									{convertPrice price=$product.price}
								</span>
							</p>
							{if $use_taxes && $display_tax_label}
							
							<!-- Grundpreis -->
							{if isset($product.unit_net) && $product.unit_net>0 && isset($product.unity) && $product.unit_price_ratio>0}
								{math equation="pprice / punit_price"  pprice=$product.price  punit_price=$product.unit_price_ratio assign=unit_price}
								<div class="gn_adds gn_unitprice"><span id="unit_price_display">{convertPrice price=$unit_price}</span> {l s='per' mod='germanext'} {$product.unity|escape:'htmlall':'UTF-8'}</div>
							{/if}
							<!-- --------- -->
								<div  class="gn_adds gn_tax">{l s='tax incl.' mod='germanext'} ({str_replace('.', ',', (string)((float)($product.rate)))}{l s='%' mod='germanext'})</div>
							{/if}
							{else}
							<p class="price_container">
								<span class="price">
								{if $product.id_product_attribute != 0 && isset($product.total_combinations) && $product.total_combinations > 0}
									<span class="gn_adds gn_pricefrom">{l s='From' mod='germanext'}</span>
								{/if} 
								{convertPrice price=$product.price_tax_exc}
								</span>
							</p>
						<div class="gn_adds gn_block"> 
							<!-- Grundpreis -->
							{if isset($product.unit_net) && $product.unit_net>0 && isset($product.unity) && $product.unit_price_ratio>0}
							   {math equation="pprice / punit_price"  pprice=$product.price_tax_exc  punit_price=$product.unit_price_ratio assign=unit_price}
								<div class="gn_adds gn_unitprice"><span id="unit_price_display">{convertPrice price=$unit_price}</span> {l s='per' mod='germanext'} {$product.unity|escape:'htmlall':'UTF-8'}</div>
							{/if}
							<!-- --------- -->
							{if $use_taxes && $display_tax_label}
								<div class="gn_adds gn_tax">{l s='tax excl.' mod='germanext'}</div>
							{/if}
							{/if}
						{else}
		                    <div style="height:21px;"></div>
	                    {/if}
					{if isset($CMS_SHIPPING_LINK) && $CMS_SHIPPING_LINK}
	                    <div class="gn_adds gn_shipping"> {l s='plus' mod='germanext'} <a style="display: inline" href="{$CMS_SHIPPING_LINK}" class="iframe fancybox">{l s='shipping costs' mod='germanext'}</a>                    </div>
					{/if}
					{if isset($product.weight) && $product.weight>0 && Configuration::get('PS_SHIPPING_METHOD')}
						<div class= "gn_adds weight"> {l s='Shipping weight:' mod='germanext'} <span> {str_replace('.', ',', (string)((float)($product.weight)))}{Configuration::get('PS_WEIGHT_UNIT')}</span></div>
					{/if} 
					</div>
					<div>

						<a class="lnk_more" href="{$product.link}" title="{l s='View' mod='germanext'}">{l s='View' mod='germanext'}</a>
						
						{if ($product.id_product_attribute == 0 OR (isset($add_prod_display) AND ($add_prod_display == 1))) AND $product.available_for_order AND !isset($restricted_country_mode) AND $product.minimal_quantity == 1 AND $product.customizable != 2 AND !$PS_CATALOG_MODE}
							{if ($product.quantity > 0 OR $product.allow_oosp)}
							<a class="exclusive ajax_add_to_cart_button" rel="ajax_id_product_{$product.id_product}" href="{$link->getPageLink('cart.php')}?qty=1&amp;id_product={$product.id_product}&amp;token={$static_token}&amp;add" title="{l s='Add to cart' mod='germanext'}">{l s='Add to cart' mod='germanext'}</a>
							{else}
							<span class="exclusive">{l s='Add to cart' mod='germanext'}</span>
							{/if}
						{else}
							<div style="height:23px;"></div>
						{/if}
					</div>
				</li>
			{/foreach}
			</ul>
		</div>
	{else}
		<p>{l s='No featured products' mod='germanext'}</p>
	{/if}
</div>
<!-- /MODULE Home Featured Products -->

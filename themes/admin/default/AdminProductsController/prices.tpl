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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14177 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{* BEGIN CUSTOMER AUTO-COMPLETE / TO REFACTO *}
<script type="text/javascript">
var Customer = new Object();
var product_url = '{$link->getAdminLink('AdminProducts', true)}';
var ecotax_tax_excl = parseFloat({$ecotax_tax_excl});
$(document).ready(function () {
	Customer = {
		"hiddenField": jQuery('#id_customer'),
		"field": jQuery('#customer'),
		"container": jQuery('#customers'),
		"loader": jQuery('#customerLoader'),
		"init": function() {
			jQuery(Customer.field).typeWatch({
				"captureLength": 1,
				"highlight": true,
				"wait": 50,
				"callback": Customer.search
			}).focus(Customer.placeholderIn).blur(Customer.placeholderOut);
		},
		"placeholderIn": function() {
			if (this.value == '{l s='All customers' mod='germanext'}') {
				this.value = '';
			}
		},
		"placeholderOut": function() {
			if (this.value == '') {
				this.value = '{l s='All customers' mod='germanext'}';
			}
		},
		"search": function()
		{
			Customer.showLoader();
			jQuery.ajax({
				"type": "POST",
				"url": "{$link->getAdminLink('AdminCustomers')}",
				"async": true,
				"dataType": "json",
				"data": {
					"ajax": "1",
					"token": "{getAdminToken tab='AdminCustomers'}",
					"tab": "AdminCustomers",
					"action": "searchCustomers",
					"customer_search": Customer.field.val()
				},
				"success": Customer.success
			});
		},
		"success": function(result)
		{
			if(result.found) {
				var html = '<ul class="clearfix">';
				jQuery.each(result.customers, function() {
					html += '<li><a class="fancybox" href="{$link->getAdminLink('AdminCustomers')}&id_customer='+this.id_customer+'&viewcustomer&liteDisplaying=1">'+this.firstname+' '+this.lastname+'</a>'+(this.birthday ? ' - '+this.birthday:'')+'<br/>';
					html += '<a href="mailto:'+this.email+'">'+this.email+'</a><br />';
					html += '<a onclick="Customer.select('+this.id_customer+', \''+this.firstname+' '+this.lastname+'\'); return false;" href="#" class="button">{l s='Choose' mod='germanext'}</a></li>';
				});
				html += '</ul>';
			}
			else
				html = '<div class="warn">{l s='No customers found' mod='germanext'}</div>';
			Customer.hideLoader();
			Customer.container.html(html);
			jQuery('.fancybox', Customer.container).fancybox();
		},
		"select": function(id_customer, fullname)
		{
			Customer.hiddenField.val(id_customer);
			Customer.field.val(fullname);
			Customer.container.empty();
			return false;
		},
		"showLoader": function() {
			Customer.loader.fadeIn();
		},
		"hideLoader": function() {
			Customer.loader.fadeOut();
		}
	};
	Customer.init();
});

function updateUnitPrice()
{
	var UnitPrice = parseFloat(document.getElementById('unit_price').value.replace(/,/g, '.'));  
	var UnitNet = parseFloat(document.getElementById('unit_net').value.replace(/,/g, '.'));  
	if (isNaN(UnitPrice) || UnitPrice < 0)  UnitPrice = 0;
	document.getElementById('unit_price').value = ps_round(UnitPrice, 2).toFixed(2);
	unitPriceWithTax('unit');
	
	if (!isNaN(UnitNet) && UnitNet > 0  )
	{
	   document.getElementById('priceTE').value = ps_round((UnitNet * UnitPrice), 2).toFixed(2);
	   calcPriceTI();
	}
}
      
function updateUnitNet()
{
	$('#unity_net_second').html( document.getElementById('unity').value );
	
	var Price = parseFloat(document.getElementById('priceTE').value.replace(/,/g, '.'));    //withoutTax
	if (isNaN(Price) || Price <= 0)
	   return;         
   
	var UnitPrice = parseFloat(document.getElementById('unit_price').value.replace(/,/g, '.'));  
	var UnitNet = parseFloat(document.getElementById('unit_net').value.replace(/,/g, '.'));  
	if (isNaN(UnitPrice) || UnitPrice < 0)  UnitPrice = 0;
	
	if (isNaN(UnitNet)   || UnitNet < 0  )  
	{
		UnitNet = 0;
		document.getElementById('unit_net').value = UnitNet;
	}
	
	UnitPrice = (UnitNet > 0) ? Price/UnitNet : 0;
	document.getElementById('unit_price').value = ps_round(UnitPrice, 2).toFixed(2);
	unitPriceWithTax('unit');
}
</script>

{* END CUSTOMER AUTO-COMPLETE / TO REFACTO *}
<input type="hidden" name="submitted_tabs[]" value="Prices" />
<h4>{l s='Product price' mod='germanext'}</h4>
<div class="hint" style="display:block;min-height:0;">
	{l s='You must enter either the pre-tax retail price, or the retail price with tax. The input field will be automatically calculated.' mod='germanext'}
</div>
{include file="controllers/products/multishop/check_fields.tpl" product_tab="Prices"}
<div class="separation"></div>
<table>
	<tr>
		<td class="col-left">
			{include file="controllers/products/multishop/checkbox.tpl" field="wholesale_price" type="default"}
			<label>{l s='Pre-tax wholesale price:' mod='germanext'}</label>
		</td>
		<td style="padding-bottom:5px;">
			{$currency->prefix}<input size="11" maxlength="14" name="wholesale_price" id="wholesale_price" type="text" value="{{toolsConvertPrice price=$product->wholesale_price}|string_format:'%.2f'}" onchange="this.value = this.value.replace(/,/g, '.');" />{$currency->suffix}
			<p class="preference_description">{l s='Wholesale price' mod='germanext'}</p>
		</td>
	</tr>

	<tr>
		<td class="col-left">
			{include file="controllers/products/multishop/checkbox.tpl" field="price" type="price"}
			<label>{l s='Pre-tax retail price:' mod='germanext'}</label>
		</td>
		<td style="padding-bottom:5px;">
			<input type="hidden"  id="priceTEReal" name="price" value="{toolsConvertPrice price=$product->price}" />
			{$currency->prefix}<input size="11" maxlength="14" id="priceTE" name="price_displayed" type="text" value="{{toolsConvertPrice price=$product->price}|string_format:'%.2f'}" onchange="noComma('priceTE'); $('#priceTEReal').val(this.value);" onkeyup="$('#priceType').val('TE'); $('#priceTEReal').val(this.value.replace(/,/g, '.')); if (isArrowKey(event)) return; calcPriceTI();" />{$currency->suffix}
			<p class="preference_description">{l s='The pre-tax retail price to sell this product' mod='germanext'}</p>
		</td>
	</tr>
	<tr>
		<td class="col-left">
			{include file="controllers/products/multishop/checkbox.tpl" field="id_tax_rules_group" type="default"}
			<label>{l s='Tax rule:' mod='germanext'}</label>
		</td>
		<td style="padding-bottom:5px;">
			<script type="text/javascript">
				noTax = {if $tax_exclude_taxe_option}true{else}false{/if};
				taxesArray = new Array ();
				taxesArray[0] = 0;
				{foreach $tax_rules_groups as $tax_rules_group}
					{if isset($taxesRatesByGroup[$tax_rules_group['id_tax_rules_group']])}
					taxesArray[{$tax_rules_group.id_tax_rules_group}] = {$taxesRatesByGroup[$tax_rules_group['id_tax_rules_group']]};
						{else}
					taxesArray[{$tax_rules_group.id_tax_rules_group}] = 0;
					{/if}
				{/foreach}
				ecotaxTaxRate = {$ecotaxTaxRate / 100};
			</script>

			<span {if $tax_exclude_taxe_option}style="display:none;"{/if} >
				 <select onChange="javascript:calcPrice(); unitPriceWithTax('unit');" name="id_tax_rules_group" id="id_tax_rules_group" {if $tax_exclude_taxe_option}disabled="disabled"{/if} >
					<option value="0">{l s='No Tax' mod='germanext'}</option>
					{foreach from=$tax_rules_groups item=tax_rules_group}
						<option value="{$tax_rules_group.id_tax_rules_group}" {if $product->getIdTaxRulesGroup() == $tax_rules_group.id_tax_rules_group}selected="selected"{/if} >
							{$tax_rules_group['name']|htmlentitiesUTF8}
						</option>
					{/foreach}
				</select>
				<a class="button" href="{$link->getAdminLink('AdminTaxRulesGroup')|escape:'htmlall':'UTF-8'}&addtax_rules_group&id_product={$product->id}" class="confirm_leave">
				<img src="../img/admin/add.gif" alt="{l s='Create' mod='germanext'}" title="{l s='Create' mod='germanext'}" /> {l s='Create' mod='germanext'}
				</a>
			</span>
			{if $tax_exclude_taxe_option}
				<span style="margin-left:10px; color:red;">{l s='Taxes are currently disabled' mod='germanext'}</span> (<b><a href="{$link->getAdminLink('AdminTaxes')|escape:'htmlall':'UTF-8'}">{l s='Tax options' mod='germanext'}</a></b>)
				<input type="hidden" value="{$product->getIdTaxRulesGroup()}" name="id_tax_rules_group" />
			{/if}
		</td>
	</tr>
	<tr {if !$ps_use_ecotax} style="display:none;"{/if}>
		<td class="col-left">
			{include file="controllers/products/multishop/checkbox.tpl" field="ecot" type="default"}
			<label>{l s='Eco-tax (tax incl.):' mod='germanext'}</label>
		</td>
		<td>
			{$currency->prefix}<input size="11" maxlength="14" id="ecotax" name="ecotax" type="text" value="{$product->ecotax|string_format:'%.2f'}" onkeyup="$('#priceType').val('TI');if (isArrowKey(event))return; calcPriceTE(); this.value = this.value.replace(/,/g, '.'); if (parseInt(this.value) > getE('priceTE').value) this.value = getE('priceTE').value; if (isNaN(this.value)) this.value = 0;" />{$currency->suffix}
			<span style="margin-left:10px">({l s='already included in price' mod='germanext'})</span>
		</td>
	</tr>
	<tr {if !$country_display_tax_label || $tax_exclude_taxe_option}style="display:none"{/if} >
		<td class="col-left"><label>{l s='Retail price with tax:' mod='germanext'}</label></td>
		<td>
			{$currency->prefix}<input size="11" maxlength="14" id="priceTI" type="text" value="" onchange="noComma('priceTI');" onkeyup="$('#priceType').val('TI');if (isArrowKey(event)) return;  calcPriceTE();" />{$currency->suffix}
			<input id="priceType" name="priceType" type="hidden" value="TE" />
		</td>
	</tr>
	<tr id="tr_unit_price" {if ! $unities}style="display: none;"{/if}>
		<td class="col-left"><label>{l s='Unit price:' mod='germanext'}</label></td>
		<td>
			{$currency->prefix} <input size="11" maxlength="14" id="unit_price" name="unit_price" type="text" value="{$unit_price|string_format:'%.2f'}"
				onkeyup="if (isArrowKey(event)) return ;this.value = this.value.replace(/,/g, '.'); unitPriceWithTax('unit');"/>{$currency->suffix}
			{l s='/' mod='germanext'} <!--<input size="6" maxlength="10" id="unity" name="unity" type="text" value="{$product->unity|htmlentitiesUTF8}" onkeyup="if (isArrowKey(event)) return ;unitySecond();" onchange="unitySecond();"/> -->
			<select onchange="unitySecond();" name="unity" id="unity">
				{foreach $unities as $unity}
					<option value="{$unity.name}" {if $unity.name == $product->unity} selected="selected"{/if}>{$unity.name}</option>
				{/foreach}
			</select>
			{if $ps_tax && $country_display_tax_label}
				<span style="margin-left:15px">{l s='or' mod='germanext'}
					{$currency->prefix}<span id="unit_price_with_tax">0.00</span>{$currency->suffix}
					{l s='/' mod='germanext' mod='germanext'} <span id="unity_second">{$product->unity}</span> {l s='with tax' mod='germanext'}
				</span>
			{/if}
			<p>{l s='e.g.  per lb' mod='germanext'}</p>
		</td>
	</tr>
	<tr id="unit_p" {if ! $unities}style="display: none;"{/if}>
		<td class="col-left"><label>{l s='Unit Net' mod='germanext'}</label></td>
		<td style="padding-bottom:5px;">
			<input size="11" maxlength="14" id="unit_net" name="unit_net" type="text" value="{$product->unit_net}" onkeyup="
			if (isArrowKey(event)) return ;
			this.value = this.value.replace(/,/g, '.');
			updateUnitNet();
			"/>
            <span id="unity_net_second">{$product->unity}</span>
 		</td>
	</tr>
	<tr {if $unities}style="display: none;"{/if}>
		<td colspan="2">
			<p class="warning warn">{l s='Please add base price units in Germanext module configuration' mod='germanext'}</p>
		</td>
	</tr>
	<tr>
		<td class="col-left">
			{include file="controllers/products/multishop/checkbox.tpl" field="on_sale" type="default"}
			<label>&nbsp;</label>
		</td>
		<td>
			<input type="checkbox" name="on_sale" id="on_sale" style="padding-top: 5px;" {if $product->on_sale}checked="checked"{/if} value="1" />&nbsp;<label for="on_sale" class="t">{l s='Display the "on sale" icon on the product page, and in the text found within the product listing.' mod='germanext'}</label>
		</td>
	</tr>
	<tr>
		<td class="col-left"><label><b>{l s='Final retail price:' mod='germanext'}</b></label></td>
		<td>
			<span {if !$country_display_tax_label}style="display:none"{/if} >
			{$currency->prefix}<span id="finalPrice" style="font-weight: bold;">0.00</span>{$currency->suffix}<span {if $ps_tax}style="display:none;"{/if}> ({l s='tax incl.' mod='germanext'})</span>
			</span>
			<span {if $ps_tax}style="display:none;"{/if} >

			{if $country_display_tax_label}
				 /
			{/if}
			{$currency->prefix}<span id="finalPriceWithoutTax" style="font-weight: bold;"></span>{$currency->suffix} {if $country_display_tax_label}({l s='tax excl.' mod='germanext'}){/if}</span>
		</td>
	</tr>
</table>
<div class="separation"></div>

{if isset($specificPriceModificationForm)}
	<h4>{l s='Specific prices' mod='germanext'}</h4>
	<div class="hint" style="display:block;min-height:0;">
		{l s='You can set specific prices for clients belonging to different groups, different countries, etc...' mod='germanext'}
	</div>
	<br />
	<a class="button bt-icon" href="#" id="show_specific_price"><img src="../img/admin/add.gif" alt="" /><span>{l s='Add a new specific price' mod='germanext'}</span></a>
	<a class="button bt-icon" href="#" id="hide_specific_price" style="display:none"><img src="../img/admin/cross.png" alt=""/><span>{l s='Cancel new specific price' mod='germanext'}</span></a>
	<br/>
	<script type="text/javascript">
	var product_prices = new Array();
	{foreach from=$combinations item='combination'}
		product_prices['{$combination.id_product_attribute}'] = '{$combination.price}';
	{/foreach}
	</script>
	<div id="add_specific_price" style="display: none;">
		<label>{l s='For:' mod='germanext'}</label>
		{if !$multi_shop}
			<div class="margin-form">
				<input type="hidden" name="sp_id_shop" value="0" />
		{else}
			<div class="margin-form">
				<select name="sp_id_shop" id="sp_id_shop">
					{if !$admin_one_shop}<option value="0">{l s='All shops' mod='germanext'}</option>{/if}
					{foreach from=$shops item=shop}
						<option value="{$shop.id_shop}">{$shop.name|htmlentitiesUTF8}</option>
					{/foreach}
				</select>
							&gt;
		{/if}
			<select name="sp_id_currency" id="spm_currency_0" onchange="changeCurrencySpecificPrice(0);">
				<option value="0">{l s='All currencies' mod='germanext'}</option>
				{foreach from=$currencies item=curr}
					<option value="{$curr.id_currency}">{$curr.name|htmlentitiesUTF8}</option>
				{/foreach}
			</select>
						&gt;
			<select name="sp_id_country" id="sp_id_country">
				<option value="0">{l s='All countries' mod='germanext'}</option>
				{foreach from=$countries item=country}
					<option value="{$country.id_country}">{$country.name|htmlentitiesUTF8}</option>
				{/foreach}
			</select>
						&gt;
			<select name="sp_id_group" id="sp_id_group">
				<option value="0">{l s='All groups' mod='germanext'}</option>
				{foreach from=$groups item=group}
					<option value="{$group.id_group}">{$group.name}</option>
				{/foreach}
			</select>
		</div>
		<label>{l s='Customer:' mod='germanext'}</label>
		<div class="margin-form">
			<input type="hidden" name="sp_id_customer" id="id_customer" value="0" />
			<input type="text" name="customer" value="{l s='All customers' mod='germanext'}" id="customer" autocomplete="off" />
			<img src="../img/admin/field-loader.gif" id="customerLoader" alt="{l s='Loading...' mod='germanext'}" style="display: none;" />
			<div id="customers"></div>
		</div>
		{if $combinations|@count != 0}
			<label>{l s='Combination:' mod='germanext'}</label>
			<div class="margin-form">
				<select id="sp_id_product_attribute" name="sp_id_product_attribute">
					<option value="0">{l s='Apply to all combinations' mod='germanext'}</option>
					{foreach from=$combinations item='combination'}
						<option value="{$combination.id_product_attribute}">{$combination.attributes}</option>
					{/foreach}
				</select>
			</div>
		{/if}
		<label>{l s='Available from:' mod='germanext'}</label>
		<div class="margin-form">
			<input class="datepicker" type="text" name="sp_from" value="" style="text-align: center" id="sp_from" /><span style="font-weight:bold; color:#000000; font-size:12px"> {l s='to' mod='germanext'}</span>
			<input class="datepicker" type="text" name="sp_to" value="" style="text-align: center" id="sp_to" />
		</div>

		<label>{l s='Starting at' mod='germanext'}</label>
		<div class="margin-form">
			<input type="text" name="sp_from_quantity" value="1" size="3" /> <span style="font-weight:bold; color:#000000; font-size:12px">{l s='unit' mod='germanext'}</span>
		</div>
		<script type="text/javascript">
			$(document).ready(function(){
				product_prices['0'] = $('#sp_current_ht_price').html();

				$('#id_product_attribute').change(function() {
					$('#sp_current_ht_price').html(product_prices[$('#id_product_attribute option:selected').val()]);
				});
				$('#leave_bprice').click(function() {
					if (this.checked)
						$('#sp_price').attr('disabled', 'disabled');
					else
						$('#sp_price').removeAttr('disabled');
 				});

				$('.datepicker').datetimepicker({
					prevText: '',
					nextText: '',
					dateFormat: 'yy-mm-dd',

					// Define a custom regional settings in order to use PrestaShop translation tools
					currentText: '{l s='Now' mod='germanext'}',
					closeText: '{l s='Done' mod='germanext'}',
					ampm: false,
					amNames: ['AM', 'A'],
					pmNames: ['PM', 'P'],
					timeFormat: 'hh:mm:ss tt',
					timeSuffix: '',
					timeOnlyTitle: '{l s='Choose Time' mod='germanext'}',
					timeText: '{l s='Time' mod='germanext'}',
					hourText: '{l s='Hour' mod='germanext'}',
					minuteText: '{l s='Minute' mod='germanext'}',
				});
			});
		</script>

		<label>{l s='Product price' mod='germanext'}
			{if $country_display_tax_label}
				{l s='(tax excl.):' mod='germanext'}
			{/if}
		</label>
		<div class="margin-form">
			<span id="spm_currency_sign_pre_0" style="font-weight:bold; color:#000000; font-size:12px">
				{$currency->prefix}
			</span>
			<input type="text" disabled="disabled" name="sp_price" id="sp_price" value="{$product->price|string_format:'%.2f'}" size="11" />
			<span id="spm_currency_sign_post_0" style="font-weight:bold; color:#000000; font-size:12px">
				{$currency->suffix}
			</span>
			<span>
				(
					{l s='Current:' mod='germanext'}
					<span id="sp_current_ht_price">{displayWtPrice p=$product->price}</span>
				)
			</span>
			<div class="hint" style="display:block;min-height:0;">
				{l s='You can set this value to 0 in order to apply the default price' mod='germanext'}
			</div>
		</div>
		<label>
			{l s='Leave base price:' mod='germanext'}
		</label>
		<div class="margin-form">
			<input id="leave_bprice" type="checkbox" value="1" checked="checked" name="leave_bprice" />
		</div>
		<label>{l s='Apply a discount of:' mod='germanext'}</label>
		<div class="margin-form">
			<input type="text" name="sp_reduction" value="0.00" size="11" />
			<select name="sp_reduction_type">
				<option selected="selected">---</option>
				<option value="amount">{l s='Amount' mod='germanext'}</option>
				<option value="percentage">{l s='Percentage' mod='germanext'}</option>
			</select>
			{l s='(if set to "amount", tax is included)' mod='germanext'}
			<p class="preference_description">{l s='The discount is applied after the tax' mod='germanext'}</p>
		</div>
	</div>

	<table style="text-align: left;width:100%" class="table" cellpadding="0" cellspacing="0" id="specific_prices_list">
		<thead>
			<tr>
				<th class="cell border" style="width: 12%;">{l s='Rule' mod='germanext'}</th>
				<th class="cell border" style="width: 12%;">{l s='Combination' mod='germanext'}</th>
				{if $multi_shop}<th class="cell border" style="width: 12%;">{l s='Shop' mod='germanext'}</th>{/if}
				<th class="cell border" style="width: 12%;">{l s='Currency' mod='germanext'}</th>
				<th class="cell border" style="width: 11%;">{l s='Country' mod='germanext'}</th>
				<th class="cell border" style="width: 13%;">{l s='Group' mod='germanext'}</th>
				<th class="cell border" style="width: 13%;">{l s='Customer' mod='germanext'}</th>
				<th class="cell border" style="width: 13%;">{l s='Fixed price' mod='germanext'}</th>
				<th class="cell border" style="width: 13%;">{l s='Impact' mod='germanext'}</th>
				<th class="cell border" style="width: 15%;">{l s='Period' mod='germanext'}</th>
				<th class="cell border" style="width: 13%;">{l s='From (quantity)' mod='germanext'}</th>
				<th class="cell border" style="width: 2%;">{l s='Action' mod='germanext'}</th>
			</tr>
		</thead>
		<tbody>
			{$specificPriceModificationForm}
				<script type="text/javascript">
					$(document).ready(function() {
						calcPriceTI();
						unitPriceWithTax('unit');
					});
				</script>
			{/if}
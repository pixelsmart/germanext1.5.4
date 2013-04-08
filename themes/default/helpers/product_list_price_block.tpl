{if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
<div class="content_price">
    {if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
    <span class="gn_from">
        {if $product.id_product_attribute > 0 && isset($product.total_combinations) && $product.total_combinations > 0}{l s='From' mod='germanext'}{/if}
    </span>
    
    <span class="price" style="display: inline;">
        {if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
    </span>
	
	<div class="gn_adds gn_block">
		{if isset($product.unit_net) && trim($product.unit_net) !="0" && isset($product.unity) && $product.unit_price_ratio > 0}
		{math equation="pprice / punit_price"  pprice=$product.price  punit_price=$product.unit_price_ratio assign=unit_price}
		<div class="gn_adds gn_unitprice">({convertPrice price=$unit_price} {l s='per' mod='germanext'} {$product.unity})</div>
		{/if}
		
		{if !$priceDisplay}
		<div class="gn_adds gn_tax">
		   <div class="gn_adds gn_tax">{l s='tax incl.' mod='germanext'} ({str_replace('.', ',', (string)((float)($product.rate)))}{l s='%' mod='germanext'})</div>
		</div>
		{/if}		
		{if isset($CMS_SHIPPING_LINK) && $CMS_SHIPPING_LINK}
		<div class="gn_adds gn_shipping"> {l s='plus' mod='germanext'}
			<a href="{$CMS_SHIPPING_LINK}" class="fancybox iframe">{l s='shipping costs' mod='germanext'}</a>
		</div>
	</div>
    {/if}
    
    {if isset($product.weight) && $product.weight > 0 && Configuration::get('PS_SHIPPING_METHOD')}
    <div  class="gn_adds weight"> {l s='Shipping weight:' mod='germanext'} {str_replace('.', ',', (string)((float)($product.weight)))} {Configuration::get('PS_WEIGHT_UNIT')}</div>
    {/if}
    
    {if isset($ustgdisp) && $ustgdisp.inlist}
    <div class="gn_adds gn_ustg">{l s='According to paragraph 19 and VAT is not displayed in the invoice' mod='germanext'}</div>
    {/if}
{/if}

    {if isset($product.available_for_order) && $product.available_for_order && !isset($restricted_country_mode)}
    <span class="availability">
        {if ($product.allow_oosp || $product.quantity > 0)}
        {l s='Available' mod='germanext'}
        {elseif (isset($product.quantity_all_versions) && $product.quantity_all_versions > 0)}
        {l s='Product available with different options' mod='germanext'}
        {else}
        {l s='Out of stock' mod='germanext'}
        {/if}
    </span>
    {/if}
</div>
{if isset($product.online_only) && $product.online_only}
<span class="online_only">{l s='Online only!' mod='germanext'}</span>
{/if}
{/if}
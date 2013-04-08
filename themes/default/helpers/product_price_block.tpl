<!-- prices -->
{if $product->show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}

	{if $product->online_only}
	<p class="online_only">{l s='Online only' mod='germanext'}</p>
	{/if}
	<div class="price">
        {if !$priceDisplay || $priceDisplay == 2}
            {assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL)}
            {assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL)}
        {elseif $priceDisplay == 1}
            {assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL)}
            {assign var='productPriceWithoutReduction' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL)}
        {/if}

        <p class="our_price_display">
        {if $priceDisplay >= 0 && $priceDisplay <= 2}
            <span id="our_price_display">{convertPrice price=$productPrice}</span>
            <!-- Base price  -->
            {if isset($unitPrice) && $unitPrice>0 && isset($product->unity)}
            <div id="our_unit_price"> 
                (<span id="our_unit_price_display">{convertPrice price=$unitPrice}</span> {l s='per' mod='germanext'} <span id="our_unity_string">{$product->unity|escape:'htmlall':'UTF-8'}</span>)
            </div>
            {/if}
            <!-- EOF Base price -->
            
        {/if}
        </p>
            <!-- Tax info -->
            {if $tax_enabled && isset($display_tax_label) && $display_tax_label}
                {if $priceDisplay == 1}
                    <span class="gn_priceadds">{l s='tax excl.' mod='germanext'}</span>
                {else}
                    <span class="gn_priceadds">{l s='tax incl.' mod='germanext'} ({str_replace('.', ',', (string)((float)($product->tax_rate)))}{l s='%' mod='germanext'}) </span>
                {/if}
            {/if}
            <!-- EOF Tax info -->

        {if $product->on_sale}
        <img src="{$img_dir}onsale_{$lang_iso}.gif" alt="{l s='On sale' mod='germanext'}" class="on_sale_img"/>
        <span class="on_sale">{l s='On sale!' mod='germanext'}</span>
		{elseif $product->specificPrice AND $product->specificPrice.reduction AND $productPriceWithoutReduction > $productPrice}
        <span class="discount">{l s='Reduced price!' mod='germanext'}</span>
        {/if}
    
        {if $priceDisplay == 2}
        <br />
        <span id="pretaxe_price">
            <span id="pretaxe_price_display">{convertPrice price=$product->getPrice(false, $smarty.const.NULL)}</span>&nbsp;{l s='tax excl.' mod='germanext'}
        </span>
        {/if}
	</div>
    
    {if $product->specificPrice AND $product->specificPrice.reduction_type == 'percentage'}
    <p id="reduction_percent">
        <span id="reduction_percent_display">-{$product->specificPrice.reduction*100}%</span>
    </p>
    {elseif $product->specificPrice AND $product->specificPrice.reduction_type == 'amount'}
    <p id="reduction_amount">
        <span id="reduction_amount_display">-{convertPrice price=$product->specificPrice.reduction|floatval}</span>
    </p>
    {/if}

    {if $product->specificPrice AND $product->specificPrice.reduction}
    <p id="old_price">
        <span class="bold">
        {if $priceDisplay >= 0 && $priceDisplay <= 2}
            {if $productPriceWithoutReduction > $productPrice}
            <span id="old_price_display">{convertPrice price=$productPriceWithoutReduction}</span>
            <!-- {if $tax_enabled && $display_tax_label == 1}
                {if $priceDisplay == 1}{l s='tax excl.' mod='germanext'}{else}{l s='tax incl.' mod='germanext'}{/if}
            {/if} -->
            {/if}
        {/if}
        </span>
    </p>
    {/if}
    
    {if $packItems|@count && $productPrice < $product->getNoPackPrice()}
    <p class="pack_price">{l s='instead of' mod='germanext'} <span style="text-decoration: line-through;">{convertPrice price=$product->getNoPackPrice()}</span></p>
    <br class="clear" />
    {/if}
    
    {if $product->ecotax != 0}
    <p class="price-ecotax">{l s='include' mod='germanext'} <span id="ecotax_price_display">{if $priceDisplay == 2}{$ecotax_tax_exc|convertAndFormatPrice}{else}{$ecotax_tax_inc|convertAndFormatPrice}{/if}</span> {l s='for green tax' mod='germanext'}
        {if $product->specificPrice AND $product->specificPrice.reduction}
        <br />{l s='(not impacted by the discount)' mod='germanext'}
        {/if}
    </p>
    {/if}
    
	<div class="gn_adds gn_block">
		{if !empty($product->unity) && $product->unit_price_ratio > 0.000000}
		{math equation="pprice / punit_price"  pprice=$productPrice  punit_price=$product->unit_price_ratio assign=unit_price}
		<div class="gn_adds unit-price">
			<span id="unit_price_display">{convertPrice price=$unit_price}</span> {l s='per' mod='germanext'} {$product->unity|escape:'htmlall':'UTF-8'}
		</div>
		{/if}
		
		<!-- shipping cost CMS link -->
		{if isset($CMS_SHIPPING_LINK) && $CMS_SHIPPING_LINK}
		<div class="gn_adds gn_shipping"> {l s='plus' mod='germanext'}
			<a href="{$CMS_SHIPPING_LINK}" class="fancybox iframe">{l s='shipping costs' mod='germanext'}</a>
		</div>
		{/if}
		
		{if isset($product->weight) && $product->weight > 0 && Configuration::get('PS_SHIPPING_METHOD')}
			<div span="gn_adds gn_weight"> {l s='Shipping weight:' mod='germanext'} <span class="shipping_weight">{str_replace('.', ',', (string)((float)($product->weight)))}</span> {Configuration::get('PS_WEIGHT_UNIT')}</div>
		{/if}
		
		<!-- EOF shipping cost CMS link -->
		{if isset($ustgdisp) && $ustgdisp.inpage}
		<div class="gn_adds gn_ustg">{l s='According to paragraph 19 and VAT is not displayed in the invoice' mod='germanext'}</div>
		{/if}
	</div>
{*close if for show price*}
{/if}

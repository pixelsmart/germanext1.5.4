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
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if $PS_CATALOG_MODE}
	{capture name=path}{l s='Your shopping cart' mod='germanext'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
	<h2 id="cart_title">{l s='Your shopping cart' mod='germanext'}</h2>
	<p class="warning">{l s='Your new order was not accepted.' mod='germanext'}</p>
{else}
<script type="text/javascript">
	// <![CDATA[
	var imgDir = '{$img_dir}';
	var authenticationUrl = '{$link->getPageLink("authentication", true)}';
	var orderOpcUrl = '{$link->getPageLink("order-opc", true)}';
	var historyUrl = '{$link->getPageLink("history", true)}';
	var guestTrackingUrl = '{$link->getPageLink("guest-tracking", true)}';
	var addressUrl = '{$link->getPageLink("address", true)}';
	var orderProcess = 'order-opc';
	var guestCheckoutEnabled = {$PS_GUEST_CHECKOUT_ENABLED|intval};
	var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
	var currencyRate = '{$currencyRate|floatval}';
	var currencyFormat = '{$currencyFormat|intval}';
	var currencyBlank = '{$currencyBlank|intval}';
	var displayPrice = {$priceDisplay};
	var taxEnabled = {$use_taxes};
	var conditionEnabled = {$conditions|intval};
	var countries = new Array();
	var countriesNeedIDNumber = new Array();
	var countriesNeedZipCode = new Array();
	var vat_management = {$vat_management|intval};
	
	var txtWithTax = "{l s='(tax incl.)' mod='germanext' js=1}";
	var txtWithoutTax = "{l s='(tax excl.)' mod='germanext' js=1}";
	var txtHasBeenSelected = "{l s='has been selected' mod='germanext' js=1}";
	var txtNoCarrierIsSelected = "{l s='No carrier has been selected' mod='germanext' js=1}";
	var txtNoCarrierIsNeeded = "{l s='No carrier is needed for this order' mod='germanext' js=1}";
	var txtConditionsIsNotNeeded = "{l s='No terms of service must be accepted' mod='germanext' js=1}";
	var txtTOSIsAccepted = "{l s='Terms of service have been accepted' mod='germanext' js=1}";
	var txtTOSIsNotAccepted = "{l s='Terms of service have not been accepted' mod='germanext' js=1}";
	var txtThereis = "{l s='There is' mod='germanext' js=1}";
	var txtErrors = "{l s='error(s)' mod='germanext' js=1}";
	var txtDeliveryAddress = "{l s='Delivery address' mod='germanext' js=1}";
	var txtInvoiceAddress = "{l s='Invoice address' mod='germanext' js=1}";
	var txtModifyMyAddress = "{l s='Modify my address' mod='germanext' js=1}";
	var txtInstantCheckout = "{l s='Instant checkout' mod='germanext' js=1}";
	var errorCarrier = "{$errorCarrier}";
	var errorTOS = "{$errorTOS}";
	var checkedCarrier = "{if isset($checked)}{$checked}{else}0{/if}";
	var PS_PRIVACY = {if isset($PS_PRIVACY)}{$PS_PRIVACY|intval}{else}0{/if};
	var addresses = new Array();
	var isLogged = {$isLogged|intval};
	var isGuest = {$isGuest|intval};
	var isVirtualCart = {$isVirtualCart|intval};
	var isPaymentStep = {$isPaymentStep|intval};
	var privacyNotAccepted = "{l s='Please accept the terms of privacy' mod='germanext' js=1}";
	var notLoggedIn = "{l s='Please log in/save your personal data first' mod='germanext' js=1}";
	//]]>
</script>
	{if $productNumber}
		{if isset($ONLY_SHIPPING_CART) && $ONLY_SHIPPING_CART}
		<!-- Shopping Cart -->
		{include file="$germanext_tpl./shopping-cart.tpl"}
		<!-- End Shopping Cart -->
		{else}
			{if ! isset($GN_CHECK_PAYMENT) || ! $GN_CHECK_PAYMENT}
			<!-- Shopping Cart -->
			{include file="$germanext_tpl./shopping-cart.tpl"}
			<!-- End Shopping Cart -->
			{/if}
			{if $isLogged AND !$isGuest}
			{include file="$tpl_dir./order-address.tpl"}
            		{else}
			<!-- Create account / Guest account / Login block -->
			{include file="$germanext_tpl./order-opc-new-account.tpl"}
			<!-- END Create account / Guest account / Login block -->
			{/if}

			<div id="loggedWrapper" {if ! $isLogged}style="display: none;"{/if}>
				<!-- Carrier -->
				{include file="$germanext_tpl./order-carrier.tpl"}
				<!-- END Carrier -->
        
				<!-- Payment -->
				{include file="$tpl_dir./order-payment.tpl"}
				<!-- END Payment -->
            
				{if isset($GN_CHECK_PAYMENT) && $GN_CHECK_PAYMENT}
				<!-- Shopping Cart -->
				{include file="$germanext_tpl./shopping-cart.tpl"}
				<!-- End Shopping Cart -->
				{/if}
			</div>
		{/if}
	{else}
		{capture name=path}{l s='Your shopping cart' mod='germanext'}{/capture}
		{include file="$tpl_dir./breadcrumb.tpl"}
		<h2>{l s='Your shopping cart' mod='germanext'}</h2>
		<p class="warning">{l s='Your shopping cart is empty.' mod='germanext'}</p>
	{/if}
{/if}

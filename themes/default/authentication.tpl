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
{capture name=path}{l s='Login' mod='germanext'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<script type="text/javascript">
// <![CDATA[
var idSelectedCountry = {if isset($smarty.post.id_state)}{$smarty.post.id_state|intval}{else}false{/if};
var countries = new Array();
var countriesNeedIDNumber = new Array();
var countriesNeedZipCode = new Array(); 
{if isset($countries)}
	{foreach from=$countries item='country'}
		{if isset($country.states) && $country.contains_states}
			countries[{$country.id_country|intval}] = new Array();
			{foreach from=$country.states item='state' name='states'}
				countries[{$country.id_country|intval}].push({ldelim}'id' : '{$state.id_state}', 'name' : '{$state.name|escape:'htmlall':'UTF-8'}'{rdelim});
			{/foreach}
		{/if}
		{if $country.need_identification_number}
			countriesNeedIDNumber.push({$country.id_country|intval});
		{/if}
		{if isset($country.need_zip_code)}
			countriesNeedZipCode[{$country.id_country|intval}] = {$country.need_zip_code};
		{/if}
	{/foreach}
{/if}
var privacyNotAccepted = "{l s='Please accept the terms of privacy' mod='germanext' js=1}";
$(function(){ldelim}
	$('.id_state option[value={if isset($smarty.post.id_state)}{$smarty.post.id_state}{else}{if isset($address)}{$address->id_state|escape:'htmlall':'UTF-8'}{/if}{/if}]').attr('selected', 'selected');

	$('#submitAccount').bind('click', function(evt){ldelim}
        if ( ! isPrivacyChecked())
        {ldelim}
            alert(privacyNotAccepted);
            
            return false;
        {rdelim}
    {rdelim});
{rdelim});
//]]>
{literal}
$(document).ready(function() {
	$('#company').blur(function(){
		vat_number();
	});
	vat_number();
	function vat_number()
	{
		if ($('#company').val() != '')
			$('#vat_number').show();
		else
			$('#vat_number').hide();
	}
});
{/literal}
</script>

<h1>{if !isset($email_create)}{l s='Log in' mod='germanext'}{else}{l s='Create an account' mod='germanext'}{/if}</h1>
{assign var='current_step' value='login'}
{include file="$tpl_dir./order-steps.tpl"}

{include file="$tpl_dir./errors.tpl"}
{assign var='stateExist' value=false}
{if !isset($email_create)}
	<script type="text/javascript">
	{literal}
	$(document).ready(function(){
		// Retrocompatibility with 1.4
		if (typeof baseUri === "undefined" && typeof baseDir !== "undefined")
		baseUri = baseDir;
		$('#create-account_form').submit(function(){
			submitFunction();
			return false;
		});
		$('#SubmitCreate').click(function(){
			submitFunction();
		});
	});
	function submitFunction()
	{
		//send the ajax request to the server
		$.ajax({
			type: 'POST',
			url: baseDir + 'index.php',
			async: true,
			cache: false,
			dataType : "json",
			data: 'controller=authentication&SubmitCreate=1&ajax=true&email_create='+$('#email_create').val()+'&token='+token,
			success: function(jsonData)
			{
				if (jsonData.hasError)
				{
					var errors = '';
					for(error in jsonData.errors)
						//IE6 bug fix
						if(error != 'indexOf')
							errors += jsonData.errors[error] + "\n";
					alert(errors);
				}
				else
				{
					// adding a div to display a transition
					$('#center_column').html('<div id="noSlide">'+$('#center_column').html()+'</div>');
					$('#noSlide').fadeOut('slow', function(){
						$('#noSlide').html(jsonData.page);
					});
					$('#noSlide').fadeIn('slow');
					document.location = '#account-creation';
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{
				alert("TECHNICAL ERROR: unable to load form.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
			}
		});
	}
	{/literal}
	</script>
	<!--{if isset($authentification_error)}
	<div class="error">
		{if {$authentification_error|@count} == 1}
			<p>{l s='There is one error' mod='germanext'} :</p>
			{else}
			<p>{l s='There are' mod='germanext'}{$account_error|@count} {l s='errors' mod='germanext'} :</p>
		{/if}
		<ol>
			{foreach from=$authentification_error item=v}
				<li>{$v}</li>
			{/foreach}
		</ol>
	</div>
	{/if}-->
	<form action="{$link->getPageLink('authentication', true)}" method="post" id="create-account_form" class="std">
		<fieldset>
			<h3>{l s='Create an account' mod='germanext'}</h3>
			<div class="form_content clearfix">
				<p class="title_block">{l s='Please enter your email address to create an account.' mod='germanext'}.</p>
				<p class="text">
					<label for="email_create">{l s='E-mail address' mod='germanext'}</label>
					<span><input type="text" id="email_create" name="email_create" value="{if isset($smarty.post.email_create)}{$smarty.post.email_create|escape:'htmlall':'UTF-8'|stripslashes}{/if}" class="account_input" /></span>
				</p>
				<p class="submit">
					{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
					<input type="submit" id="SubmitCreate" name="SubmitCreate" class="button_large" value="{l s='Create an account'}" />
					<input type="hidden" class="hidden" name="SubmitCreate" value="{l s='Create an account'}" />
				</p>
			</div>
		</fieldset>
	</form>

	<form action="{$link->getPageLink('authentication', true)}" method="post" id="login_form" class="std">
		<fieldset>
			<h3>{l s='Already registered?' mod='germanext'}</h3>
			<div class="form_content clearfix">
				<p class="text">
					<label for="email">{l s='Email address' mod='germanext'}</label>
					<span><input type="text" id="email" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email|escape:'htmlall':'UTF-8'|stripslashes}{/if}" class="account_input" /></span>
				</p>
				<p class="text">
					<label for="passwd">{l s='Password' mod='germanext'}</label>
					<span><input type="password" id="passwd" name="passwd" value="{if isset($smarty.post.passwd)}{$smarty.post.passwd|escape:'htmlall':'UTF-8'|stripslashes}{/if}" class="account_input" /></span>
				</p>
				<p class="lost_password"><a href="{$link->getPageLink('password')}">{l s='Forgot your password?' mod='germanext'}</a></p>
				<p class="submit">
					{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
					<input type="submit" id="SubmitLogin" name="SubmitLogin" class="button" value="{l s='Log in' mod='germanext'}" />
				</p>
			</div>
		</fieldset>
	</form>

	{if isset($inOrderProcess) && $inOrderProcess && $PS_GUEST_CHECKOUT_ENABLED}
	<form action="{$link->getPageLink('authentication', true, NULL, "back=$back")}" method="post" id="new_account_form" class="std clearfix">
		<fieldset>
			<h3>{l s='Instant checkout' mod='germanext'}</h3>
			<div id="opc_account_form" style="display: block; ">
				<!-- Account -->
				<p class="required text">
					<label for="guest_email">{l s='Email address' mod='germanext'} <sup>*</sup></label>
					<input type="text" class="text" id="guest_email" name="guest_email" value="{if isset($smarty.post.guest_email)}{$smarty.post.guest_email}{/if}" />
				</p>
				<p class="radio required">
					<span>{l s='Title' mod='germanext'}</span>
					{foreach from=$genders key=k item=gender}
						<input type="radio" name="id_gender" id="id_gender{$gender->id}" value="{$gender->id}"{if isset($smarty.post.id_gender) && $smarty.post.id_gender == $gender->id} checked="checked"{/if} />
						<label for="id_gender{$gender->id}" class="top">{$gender->name}</label>
					{/foreach}
				</p>
				<p class="required text">
					<label for="firstname">{l s='First name' mod='germanext'} <sup>*</sup></label>
					<input type="text" class="text" id="firstname" name="firstname" onblur="$('#customer_firstname').val($(this).val());" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{/if}" />
					<input type="hidden" class="text" id="customer_firstname" name="customer_firstname" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{/if}" />
				</p>
				<p class="required text">
					<label for="lastname">{l s='Last name' mod='germanext'} <sup>*</sup></label>
					<input type="text" class="text" id="lastname" name="lastname" onblur="$('#customer_lastname').val($(this).val());" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{/if}" />
					<input type="hidden" class="text" id="customer_lastname" name="customer_lastname" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{/if}" />
				</p>
				<p class="select">
					<span>{l s='Date of Birth' mod='germanext'}</span>
					<select id="days" name="days">
						<option value="">-</option>
						{foreach from=$days item=day}
							<option value="{$day|escape:'htmlall':'UTF-8'}" {if ($sl_day == $day)} selected="selected"{/if}>{$day|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
						{/foreach}
					</select>
				{*
						  {l s='January' mod='germanext'}
						  {l s='February' mod='germanext'}
						  {l s='March' mod='germanext'}
						  {l s='April' mod='germanext'}
						  {l s='May' mod='germanext'}
						  {l s='June' mod='germanext'}
						  {l s='July' mod='germanext'}
						  {l s='August' mod='germanext'}
						  {l s='September' mod='germanext'}
						  {l s='October' mod='germanext'}
						  {l s='November' mod='germanext'}
						  {l s='December' mod='germanext'}
					  *}
					<select id="months" name="months">
						<option value="">-</option>
						{foreach from=$months key=k item=month}
							<option value="{$k|escape:'htmlall':'UTF-8'}" {if ($sl_month == $k)} selected="selected"{/if}>{l s=$month}&nbsp;</option>
						{/foreach}
					</select>
					<select id="years" name="years">
						<option value="">-</option>
						{foreach from=$years item=year}
							<option value="{$year|escape:'htmlall':'UTF-8'}" {if ($sl_year == $year)} selected="selected"{/if}>{$year|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
						{/foreach}
					</select>
				</p>
				{if $GN_NEWSLETTER}
				<p class="checkbox">
					<input type="checkbox" name="newsletter" id="newsletter" value="1" {if isset($smarty.post.newsletter) && $smarty.post.newsletter == '1'}checked="checked"{/if} />
					<label for="newsletter">{l s='Sign up for our newsletter!' mod='germanext'}</label>
				</p>
				{/if}
				{if (isset($PS_PSTATISTIC) && $PS_PSTATISTIC == 1) && ( ! isset($GN_FORCE_STAT_GATHER) || $GN_FORCE_STAT_GATHER == 0)} 
				<p class="checkbox">
					<input type="checkbox" id="statistic" name="statistic" value="1" {if $smarty.post.statistic == 1} checked="checked"{/if} />
					<label for="statistic">{l s='I accept personal visit statistic' mod='germanext'}</label>
				</p>
				{/if}
				{if isset($PS_PRIVACY) && $PS_PRIVACY == 1} 
				<p class="checkbox">
					<input type="checkbox" name="secure" id="secure" value="1" />
					<label for="secure">{l s='I agree with the terms of the privacy.' mod='germanext'}</label> 
					<a href="{$CMS_PRIVACY_LINK}" class="iframe">{l s='(read)' mod='germanext'}</a>
					<script type="text/javascript">$('a.iframe').fancybox();</script>
				</p>
				{/if}
				<h3>{l s='Delivery address' mod='germanext'}</h3>
				{foreach from=$dlv_all_fields item=field_name}
					{if $field_name eq "company"}
						<p class="text">
							<label for="company">{l s='Company' mod='germanext'}</label>
							<input type="text" class="text" id="company" name="company" value="{if isset($smarty.post.company)}{$smarty.post.company}{/if}" />
						</p>
						{elseif $field_name eq "vat_number"}
						<div id="vat_number" style="display:none;">
							<p class="text">
								<label for="vat_number">{l s='VAT number' mod='germanext'}</label>
								<input type="text" class="text" name="vat_number" value="{if isset($smarty.post.vat_number)}{$smarty.post.vat_number}{/if}" />
							</p>
						</div>
						{elseif $field_name eq "address1"}
						<p class="required text">
							<label for="address1">{l s='Address' mod='germanext'} <sup>*</sup></label>
							<input type="text" class="text" name="address1" id="address1" value="{if isset($smarty.post.address1)}{$smarty.post.address1}{/if}" />
						</p>
						{elseif $field_name eq "postcode"}
						<p class="required postcode text">
							<label for="postcode">{l s='Zip / Postal Code' mod='germanext'} <sup>*</sup></label>
							<input type="text" class="text" name="postcode" id="postcode" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{/if}" onblur="$('#postcode').val($('#postcode').val().toUpperCase());" />
						</p>
						{elseif $field_name eq "city"}
						<p class="required text">
							<label for="city">{l s='City' mod='germanext'} <sup>*</sup></label>
							<input type="text" class="text" name="city" id="city" value="{if isset($smarty.post.city)}{$smarty.post.city}{/if}" />
						</p>
						<!--
							   if customer hasn't update his layout address, country has to be verified
							   but it's deprecated
						   -->
						{elseif $field_name eq "Country:name" || $field_name eq "country"}
						<p class="required select">
							<label for="id_country">{l s='Country' mod='germanext'} <sup>*</sup></label>
							<select name="id_country" id="id_country">
								<option value="">-</option>
								{foreach from=$countries item=v}
									<option value="{$v.id_country}" {if ($sl_country == $v.id_country)} selected="selected"{/if}>{$v.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</p>
						{elseif $field_name eq "State:name"}
						{assign var='stateExist' value=true}

						<p class="required id_state select">
							<label for="id_state">{l s='State' mod='germanext'} <sup>*</sup></label>
							<select name="id_state" id="id_state">
								<option value="">-</option>
							</select>
						</p>
						{elseif $field_name eq "phone"}
						<p class="required text">
							<label for="phone">{l s='Phone' mod='germanext'} <sup>*</sup></label>
							<input type="text" class="text" name="phone" id="phone" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{/if}" />
						</p>
					{/if}
				{/foreach}
				{if $stateExist eq false}
					<p class="required id_state select">
						<label for="id_state">{l s='State' mod='germanext'} <sup>*</sup></label>
						<select name="id_state" id="id_state">
							<option value="">-</option>
						</select>
					</p>
				{/if}
				<input type="hidden" name="alias" id="alias" value="{l s='My address' mod='germanext'}" />
				<input type="hidden" name="is_new_customer" id="is_new_customer" value="0" />
				<!-- END Account -->
			</div>
		</fieldset>
		<fieldset class="account_creation dni">
			<h3>{l s='Tax identification' mod='germanext'}</h3>

			<p class="required text">
				<label for="dni">{l s='Identification number' mod='germanext'}</label>
				<input type="text" class="text" name="dni" id="dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{/if}" />
				<span class="form_info">{l s='DNI / NIF / NIE' mod='germanext'}</span>
			</p>
		</fieldset>
		<p class="cart_navigation required submit">
			<span><sup>*</sup>{l s='Required field' mod='germanext'}</span>
			<input type="submit" class="exclusive" name="submitGuestAccount" id="submitGuestAccount" value="{l s='Continue' mod='germanext'}" />
		</p>
	</form>
	{/if}
{else}
	<!--{if isset($account_error)}
	<div class="error">
		{if {$account_error|@count} == 1}
			<p>{l s='There is one error' mod='germanext'} :</p>
			{else}
			<p>{l s='There are' mod='germanext'}{$account_error|@count} {l s='errors' mod='germanext'} :</p>
		{/if}
		<ol>
			{foreach from=$account_error item=v}
				<li>{$v}</li>
			{/foreach}
		</ol>
	</div>
	{/if}-->
<form action="{$link->getPageLink('authentication', true)}" method="post" id="account-creation_form" class="std">
	{$HOOK_CREATE_ACCOUNT_TOP}
	<fieldset class="account_creation">
		<h3>{l s='Your personal information' mod='germanext'}</h3>
		<p class="radio required">
			<span>{l s='Title' mod='germanext'}</span>
			{foreach from=$genders key=k item=gender}
				<input type="radio" name="id_gender" id="id_gender{$gender->id}" value="{$gender->id}" {if isset($smarty.post.id_gender) && $smarty.post.id_gender == $gender->id}checked="checked"{/if} />
				<label for="id_gender{$gender->id}" class="top">{$gender->name}</label>
			{/foreach}
		</p>
		<p class="required text">
			<label for="customer_firstname">{l s='First name' mod='germanext'} <sup>*</sup></label>
			<input onkeyup="$('#firstname').val(this.value);" type="text" class="text" id="customer_firstname" name="customer_firstname" value="{if isset($smarty.post.customer_firstname)}{$smarty.post.customer_firstname}{/if}" />
		</p>
		<p class="required text">
			<label for="customer_lastname">{l s='Last name' mod='germanext'} <sup>*</sup></label>
			<input onkeyup="$('#lastname').val(this.value);" type="text" class="text" id="customer_lastname" name="customer_lastname" value="{if isset($smarty.post.customer_lastname)}{$smarty.post.customer_lastname}{/if}" />
		</p>
		<p class="required text">
			<label for="email">{l s='Email' mod='germanext'} <sup>*</sup></label>
			<input type="text" class="text" id="email" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email}{/if}" />
		</p>
		{if $GN_REG_TEXT}
		<p class="text">
			{$GN_REG_TEXT}
		</p>
		{/if}
		<p class="required password">
			<label for="passwd">{l s='Password' mod='germanext'} <sup>*</sup></label>
			<input type="password" class="text" name="passwd" id="passwd" />
			<span class="form_info">{l s='(Five characters minimum)' mod='germanext'}</span>
		</p>
		<p class="select">
			<span>{l s='Date of Birth' mod='germanext'}</span>
			<select id="days" name="days">
				<option value="">-</option>
				{foreach from=$days item=day}
					<option value="{$day|escape:'htmlall':'UTF-8'}" {if ($sl_day == $day)} selected="selected"{/if}>{$day|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
				{/foreach}
			</select>
			{*
				{l s='January' mod='germanext'}
				{l s='February' mod='germanext'}
				{l s='March' mod='germanext'}
				{l s='April' mod='germanext'}
				{l s='May' mod='germanext'}
				{l s='June' mod='germanext'}
				{l s='July' mod='germanext'}
				{l s='August' mod='germanext'}
				{l s='September' mod='germanext'}
				{l s='October' mod='germanext'}
				{l s='November' mod='germanext'}
				{l s='December' mod='germanext'}
			*}
			<select id="months" name="months">
				<option value="">-</option>
				{foreach from=$months key=k item=month}
					<option value="{$k|escape:'htmlall':'UTF-8'}" {if ($sl_month == $k)} selected="selected"{/if}>{l s=$month}&nbsp;</option>
				{/foreach}
			</select>
			<select id="years" name="years">
				<option value="">-</option>
				{foreach from=$years item=year}
					<option value="{$year|escape:'htmlall':'UTF-8'}" {if ($sl_year == $year)} selected="selected"{/if}>{$year|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
				{/foreach}
			</select>
		</p>
		{if $GN_NEWSLETTER}
		<p class="checkbox">
			<input type="checkbox" name="newsletter" id="newsletter" value="1" {if isset($smarty.post.newsletter) && $smarty.post.newsletter == '1'}checked="checked"{/if}>
			<label for="newsletter">{l s='Sign up for our newsletter!' mod='germanext'}</label>
		</p>
		{/if}
		{if (isset($PS_PSTATISTIC) && $PS_PSTATISTIC == 1) && ( ! isset($GN_FORCE_STAT_GATHER) || $GN_FORCE_STAT_GATHER == 0)} 
		<p class="checkbox">
			<input type="checkbox" id="statistic" name="statistic" value="1" {if isset($smarty.post.statistic) && $smarty.post.statistic == 1} checked="checked"{/if} />
			<label for="statistic">{l s='I accept personal visit statistic' mod='germanext'}</label>
		</p>
		{/if}
		{if isset($PS_PRIVACY) && $PS_PRIVACY == 1 && isset($CMS_PRIVACY_LINK) && $CMS_PRIVACY_LINK} 
		<p class="checkbox">
			<input type="checkbox" name="secure" id="secure" value="1" {if isset($smarty.post.secure) && $smarty.post.secure == 1} checked="checked"{/if}/>
			<label for="secure">{l s='I agree with the terms of the privacy.' mod='germanext'}</label> 
			<a href="{$CMS_PRIVACY_LINK}" class="fancybox iframe">{l s='(read)' mod='germanext'}</a>
		</p>
		{/if}
	</fieldset>
	{if $b2b_enable}
	<fieldset class="account_creation">
		<h3>{l s='Your company information' mod='germanext'}</h3>
		<p class="text">
			<label for="">{l s='Company' mod='germanext'}</label>
			<input type="text" class="text" id="company" name="company" value="{if isset($smarty.post.company)}{$smarty.post.company}{/if}" />
		</p>
		<p class="text">
			<label for="siret">{l s='SIRET' mod='germanext'}</label>
			<input type="text" class="text" id="siret" name="siret" value="{if isset($smarty.post.siret)}{$smarty.post.siret}{/if}" />
		</p>
		<p class="text">
			<label for="ape">{l s='APE' mod='germanext'}</label>
			<input type="text" class="text" id="ape" name="ape" value="{if isset($smarty.post.ape)}{$smarty.post.ape}{/if}" />
		</p>
		<p class="text">
			<label for="website">{l s='Website' mod='germanext'}</label>
			<input type="text" class="text" id="website" name="website" value="{if isset($smarty.post.website)}{$smarty.post.website}{/if}" />
		</p>
	</fieldset>
	{/if}
	{if isset($PS_REGISTRATION_PROCESS_TYPE) && $PS_REGISTRATION_PROCESS_TYPE}
	<fieldset class="account_creation">
		<h3>{l s='Your address' mod='germanext'}</h3>
		{foreach from=$dlv_all_fields item=field_name}
			{if $field_name eq "company"}
				<p class="text">
					<label for="company">{l s='Company' mod='germanext'}</label>
					<input type="text" class="text" id="company" name="company" value="{if isset($smarty.post.company)}{$smarty.post.company}{/if}" />
				</p>
			{elseif $field_name eq "vat_number"}
				<div id="vat_number" style="display:none;">
					<p class="text">
						<label for="vat_number">{l s='VAT number' mod='germanext'}</label>
						<input type="text" class="text" name="vat_number" value="{if isset($smarty.post.vat_number)}{$smarty.post.vat_number}{/if}" />
					</p>
				</div>
			{elseif $field_name eq "firstname"}
				<p class="required text">
					<label for="firstname">{l s='First name' mod='germanext'} <sup>*</sup></label>
					<input type="text" class="text" id="firstname" name="firstname" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{/if}" />
				</p>
			{elseif $field_name eq "lastname"}
				<p class="required text">
					<label for="lastname">{l s='Last name' mod='germanext'} <sup>*</sup></label>
					<input type="text" class="text" id="lastname" name="lastname" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{/if}" />
				</p>
			{elseif $field_name eq "address1"}
				<p class="required text">
					<label for="address1">{l s='Address' mod='germanext'} <sup>*</sup></label>
					<input type="text" class="text" name="address1" id="address1" value="{if isset($smarty.post.address1)}{$smarty.post.address1}{/if}" />
					<span class="inline-infos">{l s='Street address, P.O. Box, Company name, etc.' mod='germanext'}</span>
				</p>
			{elseif $field_name eq "address2"}
				<p class="text">
					<label for="address2">{l s='Address (Line 2)' mod='germanext'}</label>
					<input type="text" class="text" name="address2" id="address2" value="{if isset($smarty.post.address2)}{$smarty.post.address2}{/if}" />
					<span class="inline-infos">{l s='Apartment, suite, unit, building, floor, etc...' mod='germanext'}</span>
				</p>
			{elseif $field_name eq "postcode"}
				<p class="required postcode text">
					<label for="postcode">{l s='Zip / Postal Code' mod='germanext'} <sup>*</sup></label>
					<input type="text" class="text" name="postcode" id="postcode" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{/if}" onkeyup="$('#postcode').val($('#postcode').val().toUpperCase());" />
				</p>
			{elseif $field_name eq "city"}
				<p class="required text">
					<label for="city">{l s='City' mod='germanext'} <sup>*</sup></label>
					<input type="text" class="text" name="city" id="city" value="{if isset($smarty.post.city)}{$smarty.post.city}{/if}" />
				</p>
				<!--
					if customer hasn't update his layout address, country has to be verified
					but it's deprecated
				-->
			{elseif $field_name eq "Country:name" || $field_name eq "country"}
				<p class="required select">
					<label for="id_country">{l s='Country' mod='germanext'} <sup>*</sup></label>
					<select name="id_country" id="id_country">
						<option value="">-</option>
						{foreach from=$countries item=v}
						<option value="{$v.id_country}" {if ($sl_country == $v.id_country)} selected="selected"{/if}>{$v.name|escape:'htmlall':'UTF-8'}</option>
						{/foreach}
					</select>
				</p>
			{elseif $field_name eq "State:name" || $field_name eq 'state'}
				{assign var='stateExist' value=true}
				<p class="required id_state select">
					<label for="id_state">{l s='State' mod='germanext'} <sup>*</sup></label>
					<select name="id_state" id="id_state">
						<option value="">-</option>
					</select>
				</p>
			{/if}
		{/foreach}
		{if $stateExist eq false}
			<p class="required id_state select">
				<label for="id_state">{l s='State' mod='germanext'} <sup>*</sup></label>
				<select name="id_state" id="id_state">
					<option value="">-</option>
				</select>
			</p>
		{/if}
		<p class="textarea">
			<label for="other">{l s='Additional information' mod='germanext'}</label>
			<textarea name="other" id="other" cols="26" rows="3">{if isset($smarty.post.other)}{$smarty.post.other}{/if}</textarea>
		</p>
		{if $one_phone_at_least}
 			<p class="inline-infos">{l s='You must register at least one phone number.' mod='germanext'}</p>
 		{/if}
		<p class="text">
			<label for="phone">{l s='Home phone' mod='germanext'}</label>
			<input type="text" class="text" name="phone" id="phone" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{/if}" />
		</p>
		<p class="{if $one_phone_at_least}required {/if} text">
			<label for="phone_mobile">{l s='Mobile phone' mod='germanext'}{if $one_phone_at_least} <sup>*</sup>{/if}</label>
			<input type="text" class="text" name="phone_mobile" id="phone_mobile" value="{if isset($smarty.post.phone_mobile)}{$smarty.post.phone_mobile}{/if}" />
		</p>
		<p class="required text" id="address_alias">
			<label for="alias">{l s='Assign an address alias for future reference.' mod='germanext'} <sup>*</sup></label>
			<input type="text" class="text" name="alias" id="alias" value="{if isset($smarty.post.alias)}{$smarty.post.alias}{else}{l s='My address' mod='germanext'}{/if}" />
		</p>
	</fieldset>
	<fieldset class="account_creation dni">
		<h3>{l s='Tax identification' mod='germanext'}</h3>
		<p class="required text">
			<label for="dni">{l s='Identification number' mod='germanext'} <sup>*</sup></label>
			<input type="text" class="text" name="dni" id="dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{/if}" />
			<span class="form_info">{l s='DNI / NIF / NIE' mod='germanext'}</span>
		</p>
	</fieldset>
	{/if}
	{$HOOK_CREATE_ACCOUNT_FORM}
	<p class="cart_navigation required submit">
		<input type="hidden" name="email_create" value="1" />
		<input type="hidden" name="is_new_customer" value="1" />
		{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
		<input type="submit" name="submitAccount" id="submitAccount" value="{l s='Register' mod='germanext'}" class="exclusive" />
		<span><sup>*</sup>{l s='Required field' mod='germanext'}</span>
	</p>

</form>
{/if}


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
*  @version  Release: $Revision: 6753 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!--  TAX DETAILS -->
<table style="width: 100%">
	<tr>
		<td style="width: 20%"></td>
		<td style="width: 80%">
			{if $tax_exempt}
				{l s='Exempt of VAT according section 259B of the General Tax Code.' mod='germanext'}
			{else if (count($tax_details) == 0)}
					{l s='No tax' mod='germanext'}
			{else}
			<table style="width: 70%" >
				<tr style="line-height:5px;">
					<td style="text-align: left; background-color: #4D4D4D; color: #FFF; padding-left: 10px; font-weight: bold; width: 30%">{l s='Tax' mod='germanext'}</td>
					<td style="text-align: right; background-color: #4D4D4D; color: #FFF; padding-left: 10px; font-weight: bold; width: 20%">{l s='Pre-Tax Total' mod='germanext'}</td>
					<td style="text-align: right; background-color: #4D4D4D; color: #FFF; padding-left: 10px; font-weight: bold; width: 20%">{l s='Total Tax' mod='germanext'}</td>
					<td style="text-align: right; background-color: #4D4D4D; color: #FFF; padding-left: 10px; font-weight: bold; width: 20%">{l s='Total with Tax' mod='germanext'}</td>
				</tr>
				{foreach $tax_details as $taxName => $taxData}
				<tr style="line-height:6px;background-color:{cycle values='#FFF,#DDD'};">
					<td style="width: 30%">{$taxName}</td>
					<td>{if $is_order_slip}- {/if}{displayPrice currency=$order->id_currency price=$taxData.total_net}</td>
					<td>{if $is_order_slip}- {/if}{displayPrice currency=$order->id_currency price=$taxData.total}</td>
					<td>{if $is_order_slip}- {/if}{displayPrice currency=$order->id_currency price=$taxData.total_vat}</td>
				</tr>
				{/foreach}
			</table>
			{/if}
		</td>
	</tr>
</table>
<!--  / TAX DETAILS -->
{if isset($USTG) && $USTG}
<p>{l s='According to § 19 and VAT is not displayed in the invoice.' mod='germanext'}</p>
{/if}



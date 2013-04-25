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
<table>
	<tr>
		<td style="text-align: center; font-size: 6pt; color: #444">
		{if $available_in_your_account}
			{l s='An electronic version of this invoice is available in your account. To access it, log in to our website using your e-mail address and password (which you created when placing your first order).' mod='germanext'}             
			<br />
		{/if}

		{if isset($free_text)}
			{$free_text|escape:'htmlall':'UTF-8'}<br />
		{/if}
		</td>
	</tr>
</table>
{if isset($footer_address_rows) && $footer_address_rows|@count > 0}
<div style="font-size: 6pt; color: #444">
{foreach from=$footer_address_rows item=address_row name=i}
	{if $address_row.name}
	<span>{$address_row.name}:</span> 
	{/if}
	{$address_row.value} |
{/foreach}
</div>
{/if}

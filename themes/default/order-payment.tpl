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
{if isset($PAYMENT_METHOD_LIST_ONLY) AND $PAYMENT_METHOD_LIST_ONLY}
   {foreach from=$PAYMENT_METHOD_LIST_ONLY item='method'}
   <p class="radio">
      <input id="payment_module{$method.id}" type="radio" value="{$method.id}" name="payment_module" {if $current_id_payment==$method.id}checked="checked"{/if} />
      <label for="payment_module{$method.id}" class="top">{$method.content}</label> 
   </p>
   {/foreach}
{else}
<h2>3. {l s='Choose your payment method' mod='germanext'}</h2>
<div id="opc_payment_methods" class="opc-main-block">
	<div id="opc_payment_methods-overlay" class="opc-overlay" style="display: none;"></div>
   <div id="HOOK_TOP_PAYMENT">{$HOOK_TOP_PAYMENT}</div>
    <div id="opc_payment_methods-content" {if ! $HOOK_PAYMENT}style="display: none;"{/if}>
       <div id="HOOK_PAYMENT">{if $HOOK_PAYMENT}{$HOOK_PAYMENT}{/if}</div>
    </div>
   {if ! $HOOK_PAYMENT}
      <p class="warning">{l s='No payment modules have been installed.' mod='germanext'}</p>
   {/if}
</div>
{/if}

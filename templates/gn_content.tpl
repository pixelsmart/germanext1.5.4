{literal}
<script type="text/javascript">
   $(function(){
      $('.gn_flag').click(function(){
	 if ($(this).parent('.translatable').hasClass('opened')) {
	    var langString = $('.gn_lang_str'),
	       currentString = $('.gn_lang_str_' + $(this).attr('rel')),
	       flagsToHide = $('.gn_flag').not($(this));
	    
	    if ($(this).parent('.translatable').hasClass('base_units')) {
	       langString = $(this).parents('.base_unit_block').find('.gn_lang_str'),
	       currentString = $(this).parents('.base_unit_block').find('.gn_lang_str_' + $(this).attr('rel')),
	       flagsToHide = $(this).parents('.base_unit_block').find('.gn_flag').not($(this));
	    }

	    langString.hide();
	    
	    currentString.show();
	    
	    flagsToHide.hide();
	    
	    $(this).parent('.translatable').removeClass('opened');
	 }
	 else {
	    $(this).parent('.translatable').addClass('opened').find('.gn_flag').show();
	 }
      });
   });
</script>
{/literal}


<form action="{$GN_REQUEST_URI}" method="post">

   <fieldset style="margin-top: 1em;">
    	<legend>{l s='germaNext development' mod='germanext'}</legend>
        <label><img src="../modules/germanext/logo.png"  /> </label>
        <div class="margin-form">
        	<p class="">{l s='Further project information and documentation on germanext can be found on' mod='germanext'} <a href="http://www.silbersaiten.de/redmine" target="_blank">{l s='our project development server' mod='germanext'}</a> {l s='Thank you for your bugs and features requests and for participation on project improvement!' mod='germanext'}</p>
        </div>
    </fieldset>

   <fieldset style="margin-top: 1em;">
      <legend>{l s='Payment configuration' mod='germanext'}</legend>
	  
      <label>{l s='Activate germaNext ordering process' mod='germanext'}</label>
      <div class="margin-form">
		 <input type="checkbox" name="GN_CHECK_PAYMENT" {if $GN_CHECK_PAYMENT}checked{/if}/>
		 <p class="clear">
			{l s='If you do not choose this option, the native ordering process and payment methods of prestashop will be reactivated. IMPORTANT: the native ordering process by Prestashop doesnt fit the legal security guidelines used in the German e-commerce market.' mod='germanext'}
		 </p>
      </div>
	  
      <label>{l s='Allow reordering' mod='germanext'}</label>
      <div class="margin-form">
		 <input type="checkbox" name="GN_ALLOW_REORDER" {if isset($GN_ALLOW_REORDER) && $GN_ALLOW_REORDER == 1}checked{/if}/>
		 <p class="clear">
			{l s='Allow or disallow re-placing orders in "my account"' mod='germanext'}
		 </p>
      </div>
	  
      <label>{l s='Force statistics gathering' mod='germanext'}</label>
      <div class="margin-form">
		 <input type="checkbox" name="GN_FORCE_STAT_GATHER" {if isset($GN_FORCE_STAT_GATHER) && $GN_FORCE_STAT_GATHER == 1}checked{/if}/>
		 <p class="clear">
			{l s='Note that forcing statistics gathering violates German e-commerce market security guidelines, do it on your own risk.' mod='germanext'}
		 </p>
      </div>
	  
      <label>{l s='Discount/charge settings' mod='germanext'}</label>
      <div class="margin-form">
         <table cellpadding="0" cellspacing="0" class="table">
            <thead>
               <tr>
                  <th>{l s='Payment mods' mod='germanext'}</th>
                  <th>{l s='Impact on order value' mod='germanext'}</th>
                  <th>{l s='Title' mod='germanext'}
                     <div style="float: right;" class="translatable">
                        {foreach from=$GN_LANGUAGES item=lang}
                        <div class="gn_flag" id="gn_flag_{$lang.id_lang}" rel="{$lang.id_lang}" style="display: {if $lang.id_lang==$GN_LANG_DEFAULT}block{else}none{/if};">
                           <img alt="{$lang.name}" src="../img/l/{$lang.id_lang}.jpg" style="margin: 0pt 2px;" class="pointer" />
                        </div>
                        {/foreach}
                     </div>
                  </th>
                  <th>{l s='Type' mod='germanext'}</th>
                  <th>{l s='Value' mod='germanext'}</th>
               </tr>
			</thead>
			<tbody>
			   {if $GN_PAYMENT_LIST}
               {foreach from=$GN_PAYMENT_LIST item=payment}
               <tr>
                  <th>{$payment.module}</th>
                  <td>   
                     <select name="GN_PAYMENT_IMPACT_{$payment.id_payment}" style="width:100%;">
                        <option value="0" {if $payment.impact_dir==0}selected="selected"{/if}>{l s='-- choose --' mod='germanext'}</option>
                        <option value="1" {if $payment.impact_dir==1}selected="selected"{/if}>{l s='Additional charge' mod='germanext'}</option>
                        <option value="2" {if $payment.impact_dir==2}selected="selected"{/if}>{l s='Cash discount' mod='germanext'}</option>
                     </select>
                  </td>
                  <td style="padding-bottom:5px;">
                     {foreach from=$payment.cost_name item=name}
                     <div class="gn_lang_str gn_lang_str_{$name.id_lang}" style="display: {if $name.id_lang==$GN_LANG_DEFAULT}block{else}none{/if}; float: left;">
								<input size="30" type="text" name="GN_PAYMENT_COST_NAME_{$payment.id_payment}_{$name.id_lang}" value="{$name.string}" />
							</div>
                     {/foreach}
                  </td>
                  <td>
                     <select name="GN_PAYMENT_TYPE_{$payment.id_payment}">
                        <option value="0" {if $payment.impact_type==0}selected="selected"{/if}>{l s='%' mod='germanext'}</option>
                        <option value="1" {if $payment.impact_type==1}selected="selected"{/if}>{l s='Amount' mod='germanext'}</option>
                     </select>
                  </td>
                  <td>
                     <input name="GN_PAYMENT_VALUE_{$payment.id_payment}" type="text" style="width:50px;" value="{$payment.impact_value}" /> 
                  </td>
               </tr>
               {/foreach}
			   {else}
			   <tr>
				  <td colspan="5"><p class="warning warn">{l s='No Germanext modules are installed at the time' mod='germanext'}</p></td>
			   </tr>
			   {/if}
			   <tr>
				  <th colspan="5">{l s='New Module' mod='germanext'}</th>
			   </tr>
               <tr>
                  <td>
					 <input type="text" name="newGnModule_name" value="{if isset($smarty.post.newGnModule_name)}{$smarty.post.newGnModule_name}{/if}" />
				  </td>
                  <td>   
                     <select name="newGnModule_impact" style="width:100%;">
                        <option value="0" {if isset($smarty.post.newGnModule_impact) && $smarty.post.newGnModule_impact == 0}selected="selected"{/if}>{l s='-- choose --' mod='germanext'}</option>
                        <option value="1" {if isset($smarty.post.newGnModule_impact) && $smarty.post.newGnModule_impact == 1}selected="selected"{/if}>{l s='Additional charge' mod='germanext'}</option>
                        <option value="2" {if isset($smarty.post.newGnModule_impact) && $smarty.post.newGnModule_impact == 2}selected="selected"{/if}>{l s='Cash discount' mod='germanext'}</option>
                     </select>
                  </td>
                  <td style="padding-bottom:5px;">
                     {foreach from=$languages item=language}
					 {assign var="postVar" value="newGnModule_cost_"|cat:$language.id_lang}
                     <div class="gn_lang_str gn_lang_str_{$language.id_lang}" style="display: {if $language.id_lang==$GN_LANG_DEFAULT}block{else}none{/if}; float: left;">
						<input size="30" type="text" name="newGnModule_cost_{$language.id_lang}" value="{if isset($smarty.post.$postVar)}{$smarty.post.$postVar}{/if}" />
					 </div>
                     {/foreach}
                  </td>
                  <td>
                     <select name="newGnModule_type">
                        <option value="0" {if isset($smarty.post.newGnModule_type) && $smarty.post.newGnModule_type == 0}selected="selected"{/if}>{l s='%' mod='germanext'}</option>
                        <option value="1" {if isset($smarty.post.newGnModule_type) && $smarty.post.newGnModule_type == 1}selected="selected"{/if}>{l s='Amount' mod='germanext'}</option>
                     </select>
                  </td>
                  <td>
                     <input name="newGnModule_value" type="text" style="width:50px;" value="{if isset($smarty.post.newGnModule_value)}{$smarty.post.newGnModule_value}{/if}" /> 
                  </td>
               </tr>
            </tbody>
         </table>
         <p class="clear">{l s='If you dont find your payment methods listed her, please contact the Silbersaiten support team at support@silbersaiten.de.' mod='germanext'}</p>
      </div>
	  
	  <label>{l s='Order confirmation CMS' mod='germanext'}</label>
	  <div class="margin-form">
		 <select name="mail_cms">
		 {foreach from=$GN_MAIL_CMS_TEXT item=title key=configKey}
			<option value="{$configKey}"{if isset($GN_MAIL_CMS) && $configKey == $GN_MAIL_CMS} selected="selected"{/if}>{$title}</option>
		 {/foreach}
		 </select>
		 <p class="clear">{l s='Select a CMS that you want to be added to e-mails that are being sent to customers when they place an order.' mod='germanext'}</p>
	  </div>

	  <label>{l s='"Available now" default value' mod='germanext'}</label>
	  <div class="margin-form">
		 {foreach from=$languages item=language}
		 <div id="available_now_{$language.id_lang}" style="display: {if $language.id_lang == $defaultLang}block{else}none{/if}; float: left;">
			<input size="40" type="text" id="avNow_{$language.id_lang}" name="available_now_{$language.id_lang}" value="{if isset($GN_AVAILABLE_NOW)}{$GN_AVAILABLE_NOW[$language.id_lang]}{/if}" /><sup> *</sup>
		 </div>
		 {/foreach}
		 {$avNowFlags}
		 <p class="clear"></p>
	  </div>
	  
	  <label>{l s='"Available later" default value' mod='germanext'}</label>
	  <div class="margin-form">
		 {foreach from=$languages item=language}
		 <div id="available_later_{$language.id_lang}" style="display: {if $language.id_lang == $defaultLang}block{else}none{/if}; float: left;">
			<input size="40" type="text" id="avLat_{$language.id_lang}" name="available_later_{$language.id_lang}" value="{if isset($GN_AVAILABLE_LATER)}{$GN_AVAILABLE_LATER[$language.id_lang]}{/if}" /><sup> *</sup>
		 </div>
		 {/foreach}
		 {$avLaterFlags}
		 <p class="clear"></p>
	  </div>
	  
	  <label>{l s='Registration text' mod='germanext'}</label>
	  <div class="margin-form">
		 {foreach from=$languages item=language}
		 <div id="registration_text_{$language.id_lang}" style="display: {if $language.id_lang == $defaultLang}block{else}none{/if}; float: left;">
			<textarea rows="20" cols="60" id="regText_{$language.id_lang}" name="registration_text_{$language.id_lang}">{if isset($GN_REGISTRATION_TEXT)}{$GN_REGISTRATION_TEXT[$language.id_lang]}{/if}</textarea>
		 </div>
		 {/foreach}
		 {$regTextFlags}
		 <p class="clear">{l s='This text will appear in Front Office during registration. Leave empty to disable.' mod='germanext'}</p>
	  </div>
	  
      <div class="margin-form">
         <input class="button" name="GN_BTN_SAVE" value="{l s='Save' mod='germanext'}" type="submit" />
      </div>
   </fieldset>

   <fieldset style="margin-top: 1em;">
        <div class="margin-form">
			   <iframe src="http://silbersaiten.de/page/getmoreinfo.html" frameborder="0" width="700" height="auto"></iframe>
        </div>
    </fieldset>

   <fieldset style="margin-top: 1em;">
      <legend>{l s='Trusted Shops Sample Texter' mod='germanext'}</legend>   
      <label>
		 <img src="../modules/germanext/images/tslogo.gif" />
	  </label>
	  <div class="margin-form">
		 <strong>{l s='Protection warnings: verified texts from Trusted Shops with assumption of liability' mod='germanext'}</strong>
		 <p>Schutz vor Abmahnungen: Gepr&uuml;fte Rechtstexte von Trusted Shops mit Haftungs&uuml;bernahme</p>
         <p>Der Trusted Shops Rechtstexter erstellt nach Ihren Vorgaben AGB, Widerrufs- oder R&uuml;ckgabebelehrung, Datenschutzerkl&auml;rung sowie Impressum, die auf den Bedarf in Ihrem Online-Shop zugeschnitten und individuell konfiguriert sind.</p>
         <p>Trusted Shops haftet f&uuml;r die Richtigkeit der mit dem Rechtstexter erstellten Texte. Im Falle einer Abmahnung der erzeugten Texte &uuml;bernimmt Trusted Shops die Kosten der Abmahnung oder sorgt f&uuml;r eine angemessene Rechtsverteidigung bis zur zweiten Instanz.</p>
         <p>
			<a class="linktxt" href="http://www.trustedshops.de/shop-info/rechtstexter/" target="_blank">Ausf&uuml;hrliche Informationen zum Trusted Shops Rechtstexter finden Sie hier.</a>
		 </p>
		 <br />
         <strong>{l s='Benefit from Trusted Shops special conditions for Prestashop germaNext byuers!' mod='germanext'}</strong>
         <p>{l s='Use the Trusted Shops Seal with byuers protection and save 10 Euro a month.' mod='germanext'}</p>
         <p><a class="linktxt" href="{l s='Trusted Shops Sample Texter' mod='germanext'}">{l s='learn more about the certification' mod='germanext'}</a></p>
	  </div>
   </fieldset>
</form>

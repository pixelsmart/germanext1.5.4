{if $valid_currency}
<form action="{$MP_GATEWAY_URL}" method="post" name="masterpayment" {if $MP_MODE == 'iframe'}target="masterpayment_gateway_iframe"{/if}>
	{foreach from=$API_PARAMS key=name item=value}
	<input type="hidden" name="{$name}" value="{$value}" />
	{/foreach}
</form>
{if $MP_MODE == 'iframe'}
<iframe id="masterpayment_gateway_iframe" name="masterpayment_gateway_iframe"></iframe>
{/if}

<script type="text/javascript">
        document.masterpayment.submit();
</script>
{else}
<p class="warning">
	{l s='Chosen currency was not authorized for this payment module!' mod='germanext'}
	<br />
	{l s='Please select different currency.' mod='germanext'}
</p>
{/if}

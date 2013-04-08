{if $embedScript}
<script type="text/javascript" src="{$modules_dir}germanext/payment/modules/moneybookers/moneybookers.js"></script>
{/if}
<img src="{$payment_image}" {if $payment_name}alt="{$payment_name}" title="{$payment_name}"{/if}/>
{if $payment_name}
{$payment_name}
{/if}
<input type="hidden" class="moneybookersOptions" name="payment_methods" value="{$payment_option}" />
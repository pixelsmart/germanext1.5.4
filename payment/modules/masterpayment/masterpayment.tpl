{if $embedScript}
<script type="text/javascript" src="{$modules_dir}germanext/payment/modules/masterpayment/masterpayment.js"></script>
{/if}
<img src="{$payment_image}" {if $payment_name}alt="{$payment_name}" title="{$payment_name}"{/if}/>
{if $payment_name}
{$payment_name}
{/if}
<input type="hidden" class="masterpaymentOptions" name="paymentOption" value="{$payment_option}" />
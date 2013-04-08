<script type="text/javascript">
    var checkPath = "{$smarty.const._MODULE_DIR_}directdebit/";
</script>
<script type="text/javascript" src="{$modules_dir}germanext/payment/modules/directdebit/payment.js"></script>
<div id="directDebitPaymentWrapper">
    <h3>{l s='Direct Debit Payment' mod='germanext'}</h3>
    <p class="text">
        <label>{l s='Account holder' mod='germanext'}</label>
        <input class="jsValidate" rel="isName" type="text" name="holder" />
    </p>
    
    <p class="text">
        <label>{l s='Account' mod='germanext'}</label>
        <input type="text" class="jsValidate" rel="isPhoneNumber"  name="account" />
        <span style="display: none;" class="jsValidateOnError">{l s='Please use numbers and spaces in your account number' mod='germanext'}</span>
    </p>
    
    <p class="text">
        <label>{l s='Code' mod='germanext'}</label>
        <input type="text" class="jsValidate" rel="isGenericName" name="code" />
        <span style="display: none;" class="jsValidateOnError">{l s='Please use only alphanumeric characters in "Holder" field' mod='germanext'}</span>
    </p>
    
    <p id="bankName" style="display: none;"></p>
</div>
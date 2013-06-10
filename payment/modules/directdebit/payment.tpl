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
        <p class="text">
        <label>{l s='BIC' mod='germanext'}</label>
        <input type="text" class="jsValidate" rel="isGenericName" name="bic" />
        <span style="display: none;" class="jsValidateOnError">{l s='Please use only alphanumeric characters in "BIC" field' mod='germanext'}</span>
    </p>
    <p class="text">
        <label>{l s='IBAN' mod='germanext'}</label>
        <input type="text" class="jsValidate" rel="isGenericName" name="iban" />
        <span style="display: none;" class="jsValidateOnError">{l s='Please use only alphanumeric characters in "IBAN" field' mod='germanext'}</span>
    </p>
    <div id="alertDebitAuth" class="error" style="display:none;">
        {l s='Please check debit authorisation'}
    </div>
    <p class="text">
        <input type="checkbox" name="agree" value="true" />
        <label for="agree">
            {l s='I hereby authorise <FIRM> to collect the payments to be paid by me from the present agreement by way of direct debit from my account. Charges arising out of a failure to honour a direct debit agreement plus an administration fee will be charged to the payer.'}
        </label>
    </p>
</div>
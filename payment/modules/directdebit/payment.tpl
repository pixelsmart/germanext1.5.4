<script type="text/javascript">
    var checkPath = "{$smarty.const._MODULE_DIR_}directdebit/";
</script>
<script type="text/javascript" src="{$modules_dir}germanext/payment/modules/directdebit/payment.js"></script>
<style type="text/css">
    {literal}
    #directDebitPaymentWrapper fieldset label { float: left; width: 100px; margin-right: 10px; }
    #agreelabel {width: auto; margin-left: 10px; }
    #directDebitPaymentWrapper div.text, #directDebitPaymentWrapper div.select {
        margin: 10px 0;
        margin-left: 10px;
        width: auto;
    }
    #directDebitPaymentWrapper h3 span.copyData {
        font-size: .8em;
        text-decoration: underline;
        color: #353535;
        cursor: pointer;
    }
    #directDebitPaymentWrapper h3 span.copyData:hover {
        text-decoration: none;
        color: #E64215;
    }
    div.copyContainer input {
        float: left;
    }
    div.copyContainer span.copyData {
        float: left;
        display: block;
        margin-left: 1em;
        border: 1px solid #eee;
        padding: 0 .5em;
        cursor: pointer;
    }
    p.directDebitWarn {
        overflow: hidden;
    }
    {/literal}
</style>
<div id="directDebitPaymentWrapper">
    <fieldset style="padding: 10px;">
    <h4>{l s='Direct Debit Payment' mod='germanext'}</h4>
    <table width="100%" style="">
        <tr> <td colspan="2"  style="border-color: #CCCCCC;  border-style: solid;  border-width: 1px; font-size: 11px;padding: 5px;">
                <div class="text">
                    <label for="holder">{l s='Holder:' mod='germanext'}</label>
                    <input class="jsValidate" rel="isName" type="text"  id="holder" name="holder" />
                    <span style="display: none;" class="jsValidateOnError">{l s='Please use alphanumeric characters and spaces in Holder field' mod='germanext'}</span>
                </div>
            </td></tr>
        <tr>
            <td style="border-color: #CCCCCC;  border-style: solid;  border-width: 1px; font-size: 11px;padding: 5px;">
                <div class="text">
                    <label for="account">{l s='Account:' mod='germanext'}</label>
                    <input class="jsValidate" rel="isPhoneNumber" type="text" id="account" name="account" />
                    <span style="display: none;" class="jsValidateOnError">{l s='Please use numbers and spaces in your account number' mod='germanext'}</span>
                </div>

                <div class="text">
                    <label for="code">{l s='Code:' mod='germanext'}</label>
                    <input class="jsValidate" rel="isGenericName" type="text" id="code" name="code" />
                    <span style="display: none;" class="jsValidateOnError">{l s='Please use only alphanumeric characters in "Code" field' mod='germanext'}</span>
                </div>
                <p class="text" id="bankName" style="display: none;">
                </p>
            </td>
            <td style="border-color: #CCCCCC;  border-style: solid;  border-width: 1px; font-size: 11px;padding: 5px;">
                <div class="text">
                    <label for="bic">{l s='BIC:' mod='germanext'}</label>
                    <input class="jsValidate" rel="isGenericName" type="text" id="bic" name="bic" />
                    <span style="display: none;" class="jsValidateOnError">{l s='Please use only alphanumeric characters in "BIC" field' mod='germanext'}</span>
                </div>

                <div class="text">
                    <label for="iban">{l s='IBAN:' mod='germanext'}</label>
                    <input class="jsValidate" rel="isGenericName" type="text" id="iban"  name="iban" />
                    <span style="display: none;" class="jsValidateOnError">{l s='Please use only alphanumeric characters in "IBAN" field' mod='germanext'}</span>
                </div>
            </td>
        <tr>
    </table>
    </fieldset>
    <div id="alertDebitAuth" class="error" style="display:none;">
        {l s='Please check debit authorisation' mod='germanext'}
    </div>
    <div class="text">
        <input type="checkbox" id="agree" name="agree" value="true" />
        <label id="agreelabel" for="agree">{l s='I hereby authorise to collect the payments to be paid by me from the present agreement by way of direct debit from my account. Charges arising out of a failure to honour a direct debit agreement plus an administration fee will be charged to the payer.' mod='germanext'}
        </label>
    </div>

    <div class="warning">{l s='Please fill in either both Holder and Code data, or both the IBAN and BIC data' mod='germanext'}</div>

    <div class="text">
        {l s='Please confirm your payment of ' mod='directdebit'} <strong>{convertPriceWithCurrency price=$total currency=$currency}</strong> {l s=' by clicking \'Submit Order\'' mod='germanext'}.
    </div>
</div>
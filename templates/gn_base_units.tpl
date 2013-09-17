<script type="text/javascript">
    var deleteRowStr = "{l s='Delete this base unit' mod='deliveryservice' js=1}";
</script>
<script type="text/javascript" src="{$GN_PATH}js/admin/duplicator.js"></script>
<form action="{$GN_REQUEST_URI}" method="post">
    <fieldset style="margin-top: 1em;">
		<legend>{l s='Base Units' mod='germanext'}</legend>
        <label for="base_units">{l s='Base Units' mod='germanext'}</label>
        <div class="margin-form duplicatedFields reg_t-n-d-d" rel="base_units">
        {counter start=0 assign=index name=base_units_counter print=false}
        {if $base_units}
        {foreach from=$base_units item=base_unit name=i}
            <div class="duplicatedRow {if $smarty.foreach.i.index % 2}odd{else}even{/if}_row">
		<div class="base_unit_block">
		    <div class="base_unit_name">
			{foreach from=$languages item=language}
			<input type="text" class="gn_lang_str gn_lang_str_{$language.id_lang}" {if $language.id_lang != $defaultLang}style="display: none;"{/if} name="base_units[{$index}][{$base_unit.id_base_unit}][{$language.id_lang}]" value="{$base_unit.name[$language.id_lang]}" />
			{/foreach}
		    </div>
		    
		    <div class="translatable base_units">
		    {foreach from=$languages item=language}
			<div class="gn_flag" id="gn_flag_{$language.id_lang}" rel="{$language.id_lang}" style="display: {if $language.id_lang==$defaultLang}block{else}none{/if};">
			    <img alt="{$language.name}" src="../img/l/{$language.id_lang}.jpg" style="margin: 0pt 2px;" class="pointer" />
			</div>
		    {/foreach}
		    </div>
		</div>
		<a class="deleteRow">{l s='Delete this base unit' mod='germanext'}</a>
	    </div>
	    {counter name=base_units_counter}
        {/foreach}
	{else}
	    <p class="warning">{l s='No base units were added yet' mod='germanext'}</p>
        {/if}
            <div class="duplicatedRow rowToAdd">
		<div class="base_unit_block">
		    <div class="base_unit_name">
			{foreach from=$languages item=language}
			    <input type="text" class="gn_lang_str gn_lang_str_{$language.id_lang}" {if $language.id_lang != $defaultLang}style="display: none;"{/if} name="base_units[{$index}][0][{$language.id_lang}]" value="" />
			{/foreach}
		    </div>
		    
		    <div class="translatable base_units">
		    {foreach from=$languages item=language}
			<div class="gn_flag" id="gn_flag_{$language.id_lang}" rel="{$language.id_lang}" style="display: {if $language.id_lang==$defaultLang}block{else}none{/if};">
			    <img alt="{$language.name}" src="../img/l/{$language.id_lang}.jpg" style="margin: 0pt 2px;" class="pointer" />
			</div>
		    {/foreach}
		    </div>
		</div>
            </div>
            <a class="addField" href="#">
                <img src="../img/admin/add.gif" alt="{l s='Add a base unit' mod='germanext'}" title="{l s='Add a base unit' mod='germanext'}">
            </a>
        </div>
		<div class="margin-form">
			<input type="submit" class="button" name="saveBaseUnits" value="{l s='Save' mod='germanext'}">
		</div>
    </fieldset>
</form>

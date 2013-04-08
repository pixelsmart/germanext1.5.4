<script type="text/javascript">
    var deleteRowStr = "{l s='Delete this base unit' mod='deliveryservice' js=1}";
</script>
<script type="text/javascript" src="{$GN_PATH}js/admin/duplicator.js"></script>
<form action="{$GN_REQUEST_URI}" method="post">
    <fieldset style="margin-top: 1em;">
		<legend>{l s='Base Units' mod='germanext'}</legend>
        <label for="base_units">{l s='Base Units' mod='germanext'}</label>
        <div class="margin-form duplicatedFields" rel="postcodes">
        {counter start=0 assign=index name=base_units_counter print=false}
        {if $base_units}
        {foreach from=$base_units item=base_unit name=i}
            <div class="duplicatedRow {if $smarty.foreach.i.index % 2}odd{else}even{/if}_row">
				<input type="text" name="base_units[{$index}][{$base_unit.id_base_unit}]" value="{$base_unit.name}" />
				<a class="deleteRow">{l s='Delete this base unit' mod='germanext'}</a>
			</div>
			{counter name=base_units_counter}
        {/foreach}
		{else}
		<p class="warning">{l s='No base units were added yet' mod='germanext'}</p>
        {/if}
            <div class="duplicatedRow rowToAdd">
                <input type="text" name="base_units[{$index}][0]" value="" />
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
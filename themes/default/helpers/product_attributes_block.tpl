{if isset($groups)}
<!-- attributes -->
<div id="attributes">
{foreach from=$groups key=id_attribute_group item=group}
    {if $group.attributes|@count}
    <fieldset class="attribute_fieldset">
        <label class="attribute_label" for="group_{$id_attribute_group|intval}">{$group.name|escape:'htmlall':'UTF-8'} :</label>
        {assign var="groupName" value="group_$id_attribute_group"}
        <div class="attribute_list">
        {if ($group.group_type == 'select')}
            <select name="{$groupName}" id="group_{$id_attribute_group|intval}" class="attribute_select" onchange="findCombination();getProductAttribute();{if $colors|@count > 0}$('#wrapResetImages').show('slow');{/if};">
            {foreach from=$group.attributes key=id_attribute item=group_attribute}
                <option value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $group.default == $id_attribute} selected="selected"{/if} title="{$group_attribute|escape:'htmlall':'UTF-8'}">{$group_attribute|escape:'htmlall':'UTF-8'}</option>
            {/foreach}
            </select>
        {elseif ($group.group_type == 'color')}
            <ul id="color_to_pick_list" class="clearfix">
            {assign var="default_colorpicker" value=""}
            {foreach from=$group.attributes key=id_attribute item=group_attribute}
                <li>
                    <a id="color_{$id_attribute|intval}" class="color_pick{if ($group.default == $id_attribute)} selected{/if}" style="background: {$colors.$id_attribute.value};" title="{$colors.$id_attribute.name}" onclick="colorPickerClick(this);getProductAttribute();{if $colors|@count > 0}$('#wrapResetImages').show('slow');{/if}">
                    {if file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}
                        <img src="{$img_col_dir}{$id_attribute}.jpg" alt="{$colors.$id_attribute.name}" width="20" height="20" /><br>
                    {/if}
                    </a>
                </li>
                {if ($group.default == $id_attribute)}
                    {$default_colorpicker = $id_attribute}
                {/if}
            {/foreach}
            </ul>
            <input type="hidden" id="color_pick_hidden" name="{$groupName}" value="{$default_colorpicker}" >
            {elseif ($group.group_type == 'radio')}
                {foreach from=$group.attributes key=id_attribute item=group_attribute}
                    <input type="radio" class="attribute_radio" name="{$groupName}" value="{$id_attribute}" {if ($group.default == $id_attribute)} checked="checked"{/if} onclick="findCombination();getProductAttribute();{if $colors|@count > 0}$('#wrapResetImages').show('slow');{/if}">
                    {$group_attribute|escape:'htmlall':'UTF-8'}<br/>
                {/foreach}
            {/if}
        </div>
    </fieldset>
    {/if}
{/foreach}
</div>
{/if}
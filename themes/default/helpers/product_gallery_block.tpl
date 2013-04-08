<!-- product img-->
<div id="image-block">
{if $have_image}
    <span id="view_full_size">
        <img src="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'large_default')}" {if $jqZoomEnabled}class="jqzoom" alt="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'thickbox_default')}"{else} title="{$product->name|escape:'htmlall':'UTF-8'}" alt="{$product->name|escape:'htmlall':'UTF-8'}" {/if} id="bigpic" width="{$largeSize.width}" height="{$largeSize.height}" />
        <span class="span_link">{l s='Maximize' mod='germanext'}</span>
    </span>
{else}
    <span id="view_full_size">
        <img src="{$img_prod_dir}{$lang_iso}-default-large_default.jpg" id="bigpic" alt="" title="{$product->name|escape:'htmlall':'UTF-8'}" width="{$largeSize.width}" height="{$largeSize.height}" />
        <span class="span_link">{l s='Maximize' mod='germanext'}</span>
    </span>
{/if}
</div>

{if isset($images) && count($images) > 0}
<!-- thumbnails -->
<div id="views_block" class="clearfix {if isset($images) && count($images) < 2}hidden{/if}">

{if isset($images) && count($images) > 3}
<span class="view_scroll_spacer">
    <a id="view_scroll_left" class="hidden" title="{l s='Other views' mod='germanext'}" href="javascript:{ldelim}{rdelim}">{l s='Previous' mod='germanext'}</a>
</span>
{/if}

<div id="thumbs_list">
    <ul id="thumbs_list_frame">
        {if isset($images)}
            {foreach from=$images item=image name=thumbnails}
            {assign var=imageIds value="`$product->id`-`$image.id_image`"}
            <li id="thumbnail_{$image.id_image}">
                <a href="{$link->getImageLink($product->link_rewrite, $imageIds, thickbox_default)}" rel="other-views" class="thickbox {if $smarty.foreach.thumbnails.first}shown{/if}" title="{$image.legend|htmlspecialchars}">
                    <img id="thumb_{$image.id_image}" src="{$link->getImageLink($product->link_rewrite, $imageIds, 'medium_default')}" alt="{$image.legend|htmlspecialchars}" height="{$mediumSize.height}" width="{$mediumSize.width}" />
                </a>
            </li>
            {/foreach}
        {/if}
    </ul>
</div>
{if isset($images) && count($images) > 3}
<a id="view_scroll_right" title="{l s='Other views' mod='germanext'}" href="javascript:{ldelim}{rdelim}">{l s='Next' mod='germanext'}</a>
{/if}
</div>
{/if}

{if isset($images) && count($images) > 1}
<p class="resetimg clear">
    <span id="wrapResetImages" style="display: none;">
        <img src="{$img_dir}icon/cancel_11x13.gif" alt="{l s='Cancel' mod='germanext'}" width="11" height="13"/>
        <a id="resetImages" href="{$link->getProductLink($product)}" onclick="$('span#wrapResetImages').hide('slow');return (false);">{l s='Display all pictures' mod='germanext'}</a>
    </span>
</p>
{/if}

<!-- usefull links-->
<ul id="usefull_link_block">
    {if $HOOK_EXTRA_LEFT}{$HOOK_EXTRA_LEFT}{/if}
    <li class="print"><a href="javascript:print();">{l s='Print' mod='germanext'}</a></li>
    {if $have_image && !$jqZoomEnabled}
    {/if}
</ul>
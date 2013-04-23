{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}
<script type="text/javascript">
	function saveCustomerNote() {
		$('#note_feedback').html('<img src="../img/loader.gif" />').show();
		var noteContent = $('#noteContent').val();
	
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: "submitCustomerNote=1&id_customer={$customer->id}&note="+noteContent,
			async : true,
			success: function(r) {
				$('#note_feedback').html('').hide();

				if (r == 'ok') {
					$('#note_feedback').html("<b style='color:green'>{l s='Your note has been saved.' mod='germanext'}</b>").fadeIn(400);
					$('#submitCustomerNote').attr('disabled', 'disabled');
				}
				else if (r == 'error:validation') {
					$('#note_feedback').html("<b style='color:red'>({l s='Error: your note is not valid.' mod='germanext'}</b>").fadeIn(400);
				}
				else if (r == 'error:update') {
					$('#note_feedback').html("<b style='color:red'>{l s='Error: cannot save your note.' mod='germanext'}</b>").fadeIn(400);
				}

				$('#note_feedback').fadeOut(3000);
			}
		});
	}
</script>
<div id="container-customer">
	<div class="info-customer-left">
		<div style="float: right">
			<a href="{$current}&addcustomer&id_customer={$customer->id}&token={$token}">
				<img src="../img/admin/edit.gif" />
			</a>
		</div>
		<span style="font-size: 14px;">
			{$customer->firstname} {$customer->lastname}
		</span>
		<img src="{$gender_image}" style="margin-bottom: 5px" /><br />
		<a href="mailto:{$customer->email}" style="text-decoration: underline; color:#268CCD;">{$customer->email}</a>
		<br /><br />
		{l s='ID:' mod='germanext'} {$customer->id|string_format:"%06d"}<br />
		{l s='Registration date:' mod='germanext'} {$registration_date}<br />
		{l s='Last visit:' mod='germanext'} {if $customer_stats['last_visit']}{$last_visit}{else}{l s='Never' mod='germanext'}{/if}<br />
		{if $count_better_customers != '-'}{l s='Rank: #' mod='germanext'} {$count_better_customers}<br />{/if}
		{if $shop_is_feature_active}{l s='Shop:' mod='germanext'} {$name_shop}<br />{/if}
	</div>
	
	<div class="info-customer-right">
		<div style="float: right">
			<a href="{$current}&updatecustomer&id_customer={$customer->id}&token={$token}">
				<img src="../img/admin/edit.gif" />
			</a>
		</div>
		{l s='Language:'} {if isset($customerLanguage)}{$customerLanguage->name}{else}{l s='undefined'}{/if}<br />
		{l s='Newsletter:' mod='germanext'} {if $customer->newsletter}<img src="../img/admin/enabled.gif" />{else}<img src="../img/admin/disabled.gif" />{/if}<br />
		{l s='Opt in:' mod='germanext'} {if $customer->optin}<img src="../img/admin/enabled.gif" />{else}<img src="../img/admin/disabled.gif" />{/if}<br />
		{l s='Statistic:' mod='germanext'} {if $customer->statistic}<img src="../img/admin/enabled.gif" />{else}<img src="../img/admin/disabled.gif" />{/if}<br />
		{l s='Age:' mod='germanext'} {$customer_stats['age']} {if isset($customer->birthday['age'])}({$customer_birthday}){else}{l s='Unknown' mod='germanext'}{/if}<br /><br />
		{l s='Last update:' mod='germanext'} {$last_update}<br />
		{l s='Status:' mod='germanext'} {if $customer->active}<img src="../img/admin/enabled.gif" />{else}<img src="../img/admin/disabled.gif" />{/if}
	
		{if $customer->isGuest()}
			<div>
				{l s='This customer is registered as' mod='germanext'} <b>{l s='guest' mod='germanext'}</b>
				{if !$customer_exists}
					<form method="POST" action="index.php?tab=AdminCustomers&id_customer={$customer->id}&token={getAdminToken tab='AdminCustomers'}">
						<input type="hidden" name="id_lang" value="{$id_lang}" />
						<p class="center"><input class="button" type="submit" name="submitGuestToCustomer" value="{l s='Transform to a customer account' mod='germanext'}" /></p>
						{l s='This feature generates a random password before sending an email to your customer.' mod='germanext'}
					</form>
				{else}
					</div><div><b style="color:red;">{l s='A registered customer account using the defined email address already exists. ' mod='germanext'}</b>
				{/if}
			</div>
		{/if}
	</div>
	<div class="clear"></div>
	<div class="separation"></div>
	
	<div>
		<h2>
			<img src="../img/admin/cms.gif" /> {l s='Add a private note' mod='germanext'}
		</h2>
		<p>{l s='This note will be displayed to all employees but not to customers.' mod='germanext'}</p>
		<form action="ajax.php" method="post" onsubmit="saveCustomerNote();return false;" id="customer_note">
			<textarea name="note" id="noteContent" style="width:600px;height:100px" onkeydown="$('#submitCustomerNote').removeAttr('disabled');">{$customer_note}</textarea><br />
			<input type="submit" id="submitCustomerNote" class="button" value="{l s='Save   ' mod='germanext'}" style="float:left;margin-top:5px" disabled="disabled" />
			<span id="note_feedback" style="position:relative; top:10px; left:10px;"></span>
		</form>
	</div>
	<div class="clear"></div>
	<div class="separation"></div>
	
	<div style="width:50%;float:left;">
		<h2>{l s='Messages' mod='germanext'} ({count($messages)})</h2>
		{if count($messages)}
			<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
				<tr>
					<th class="center">{l s='Status' mod='germanext'}</th>
					<th class="center">{l s='Message' mod='germanext'}</th>
					<th class="center">{l s='Sent on' mod='germanext'}</th>
				</tr>
				{foreach $messages AS $message}
				<tr>
					<td>{$message['status']}</td>
					<td>
						<a href="index.php?tab=AdminCustomerThreads&id_customer_thread={$message.id_customer_thread}&viewcustomer_thread&token={getAdminToken tab='AdminCustomerThreads'}">
							{$message['message']}...
						</a>
					</td>
					<td>{$message['date_add']}</td>
				</tr>
				{/foreach}
			</table>
			<div class="clear">&nbsp;</div>
		{else}
			{l s='%1$s %2$s has never contacted you' sprintf=[$customer->firstname, $customer->lastname]}
		{/if}
	</div>
	
	<div style="width:50%;float:left;">
		<div style="margin-left:15px;">
			<h2>{l s='Vouchers' mod='germanext'} ({count($discounts)})</h2>
			{if count($discounts)}
				<table cellspacing="0" cellpadding="0" class="table">
					<tr>
						<th>{l s='ID' mod='germanext'}</th>
						<th>{l s='Code' mod='germanext'}</th>
						<th>{l s='Name' mod='germanext'}</th>
						<th>{l s='Status' mod='germanext'}</th>
						<th>{l s='Actions' mod='germanext'}</th>
					</tr>
				{foreach $discounts AS $key => $discount}
					<tr {if $key %2}class="alt_row"{/if}>
						<td align="center">{$discount['id_cart_rule']}</td>
						<td>{$discount['code']}</td>
						<td>{$discount['name']}</td>
						<td align="center"><img src="../img/admin/{if $discount['active']}enabled.gif{else}disabled.gif{/if}" alt="{l s='Status' mod='germanext'}" title="{l s='Status' mod='germanext'}" /></td>
						<td align="center">
							<a href="?tab=AdminCartRules&id_cart_rule={$discount['id_cart_rule']}&addcart_rule&token={getAdminToken tab='AdminCartRules'}"><img src="../img/admin/edit.gif" /></a>
							<a href="?tab=AdminCartRules&id_cart_rule={$discount['id_cart_rule']}&deletecart_rule&token={getAdminToken tab='AdminCartRules'}"><img src="../img/admin/delete.gif" /></a>
						</td>
					</tr>
				{/foreach}
				</table>
			{else}
				{$customer->firstname} {$customer->lastname} {l s='has no discount vouchers' mod='germanext'}.
			{/if}
		</div>
	</div>
	
	{* display hook specified to this page : AdminCustomers *}
	<div>{hook h="displayAdminCustomers" id_customer=$customer->id}</div>
	
	<div style="width:50%;float:left;">
		<h2>{l s='Orders' mod='germanext'} ({count($orders)})</h2>
		{if $orders AND count($orders)}
			{assign var=count_ok value=count($orders_ok)}
			{if $count_ok}
				<div>
					<h3 style="color:green;font-weight:700;clear:both;">
						{l s='Valid orders:' mod='germanext'} {$count_ok} {l s='for' mod='germanext'} {$total_ok}
					</h3>
					<table cellspacing="0" cellpadding="0" class="table" style="width:100%; text-align:left;">
						<colgroup>
							<col width="10px"></col>
							<col width="100px"></col>
							<col width="100px"></col>
							<col width=""></col>
							<col width="50px"></col>
							<col width="80px"></col>
							<col width="70px"></col>
						</colgroup>
						<tr>
							<th height="39px" class="center">{l s='ID' mod='germanext'}</th>
							<th class="left">{l s='Date' mod='germanext'}</th>
							<th class="left">{l s='Payment: ' mod='germanext'}</th>
							<th class="left">{l s='State' mod='germanext'}</th>
							<th class="right">{l s='Products' mod='germanext'}</th>
							<th class="right">{l s='Total spent' mod='germanext'}</th>
							<th class="center">{l s='Actions' mod='germanext'}</th>
						</tr>
						{foreach $orders_ok AS $key => $order}
						<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '?tab=AdminOrders&id_order={$order['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}'">
							<td class="left">{$order['id_order']}</td>
							<td class="left">{$order['date_add']}</td>
							<td class="left">{$order['payment']}</td>
							<td class="left">{$order['order_state']}</td>
							<td align="right">{$order['nb_products']}</td>
							<td align="right">{$order['total_paid_real']}</td>
							<td align="center"><a href="?tab=AdminOrders&id_order={$order['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}"><img src="../img/admin/details.gif" /></a></td>
						</tr>
						{/foreach}
					</table>
				</div>
			{/if}
			{assign var=count_ko value=count($orders_ko)}
			{if $count_ko}
				<div>
					<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
						<colgroup>
							<col width="10px"></col>
							<col width="100px"></col>
							<col width=""></col>
							<col width=""></col>
							<col width="100px"></col>
							<col width="100px"></col>
							<col width="52px"></col>
						</colgroup>
						<tr>
							<th height="39px" class="left">{l s='ID' mod='germanext'}</th>
							<th class="left">{l s='Date' mod='germanext'}</th>
							<th class="left">{l s='Payment' mod='germanext'}</th>
							<th class="left">{l s='State' mod='germanext'}</th>
							<th class="right">{l s='Products' mod='germanext'}</th>
							<th class="right">{l s='Total spent' mod='germanext'}</th>
							<th class="center">{l s='Actions' mod='germanext'}</th>
						</tr>
						{foreach $orders_ko AS $key => $order}
						<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '?tab=AdminOrders&id_order={$order['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}'">
							<td class="left">{$order['id_order']}</td>
							<td class="left">{$order['date_add']}</td>
							<td class="left">{$order['payment']}</td>
							<td class="left">{$order['order_state']}</td>
							<td align="right">{$order['nb_products']}</td>
							<td align="right">{$order['total_paid_real']}</td>
							<td align="center"><a href="?tab=AdminOrders&id_order={$order['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}"><img src="../img/admin/details.gif" /></a></td>
						</tr>
						{/foreach}
					</table>
					<h3 style="color:red;font-weight:normal;">{l s='Invalid orders:' mod='germanext'} {$count_ko}</h3>
				</div>
			{/if}
		{else}
			{$customer->firstname} {$customer->lastname} {l s='has not placed any orders yet' mod='germanext'}
		{/if}
	</div>

	{if ((isset($PS_PSTATISTIC) && $PS_PSTATISTIC == 1 && isset($customer->statistic) && $customer->statistic == 1) || (isset($GN_FORCE_STAT_GATHER) && $GN_FORCE_STAT_GATHER == 1))}
	<div style="float:left;width:50%">
		<div style="margin-left:15px;">
			<h2>{l s='Carts' mod='germanext'} ({count($carts)})</h2>
			{if $carts AND count($carts)}
				<table cellspacing="0" cellpadding="0" class="table" style="width:100%">
					<colgroup>
						<col width="50px"></col>
						<col width="150px"></col>
						<col width=""></col>
						<col width="70px"></col>
						<col width="50px"></col>
					</colgroup>
					<tr>
						<th height="39px" class="center">{l s='ID' mod='germanext'}</th>
						<th class="center">{l s='Date' mod='germanext'}</th>
						<th class="center">{l s='Carrier' mod='germanext'}</th>
						<th class="center">{l s='Total' mod='germanext'}</th>
						<th class="center">{l s='Actions' mod='germanext'}</th>
					</tr>
					{foreach $carts AS $key => $cart}
						<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '?tab=AdminCarts&id_cart={$cart['id_cart']}&viewcart&token={getAdminToken tab='AdminCarts'}'">
							<td class="center">{$cart['id_cart']}</td>
							<td>{$cart['date_add']}</td>
							<td>{$cart['name']}</td>
							<td align="right">{$cart['total_price']}</td>
							<td align="center"><a href="index.php?tab=AdminCarts&id_cart={$cart['id_cart']}&viewcart&token={getAdminToken tab='AdminCarts'}"><img src="../img/admin/details.gif" /></a></td>
						</tr>
					{/foreach}
				</table>
			{else}
				{l s='No cart available' mod='germanext'}.
			{/if}
		</div>
	</div>
	<div class="clear">&nbsp;</div>	
	{/if}

	{if ((isset($PS_PSTATISTIC) && $PS_PSTATISTIC == 1 && isset($customer->statistic) && $customer->statistic == 1) || (isset($GN_FORCE_STAT_GATHER) && $GN_FORCE_STAT_GATHER == 1)) && $products && count($products)}
	<div class="clear">&nbsp;</div>
		<h2>{l s='Products:' mod='germanext'} ({count($products)})</h2>
		<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
					<colgroup>
						<col width="50px"></col>
						<col width=""></col>
						<col width="60px"></col>
						<col width="70px"></col>
					</colgroup>
			<tr>
				<th height="39px" class="center">{l s='Date' mod='germanext'}</th>
				<th class="center">{l s='Name' mod='germanext'}</th>
				<th class="center">{l s='Quantity' mod='germanext'}</th>
				<th class="center">{l s='Actions' mod='germanext'}</th>
			</tr>
			{foreach $products AS $key => $product}
				<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '?tab=AdminOrders&id_order={$product['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}'">
					<td>{$product['date_add']}</td>
					<td>{$product['product_name']}</td>
					<td align="right">{$product['product_quantity']}</td>
					<td align="center"><a href="?tab=AdminOrders&id_order={$product['id_order']}&vieworder&token={getAdminToken tab='AdminOrders'}"><img src="../img/admin/details.gif" /></a></td>
				</tr>
			{/foreach}
		</table>
	{/if}
	<div class="clear">&nbsp;</div>
	
	<div style="float:left;width:50%">
		<h2>{l s='Addresses' mod='germanext'} ({count($addresses)})</h2>
		{if count($addresses)}
			<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
						<colgroup>
							<col width="120px"></col>
							<col width="120px"></col>	
							<col width=""></col>
							<col width="100px"></col>
							<col width="170px"></col>
							<col width="70px"></col>
						</colgroup>
				<tr>
					<th height="39px">{l s='Company' mod='germanext'}</th>
					<th>{l s='Name' mod='germanext'}</th>
					<th>{l s='Address' mod='germanext'}</th>
					<th>{l s='Country' mod='germanext'}</th>
					<th>{l s='Phone number(s)' mod='germanext'}</th>
					<th>{l s='Actions' mod='germanext'}</th>
				</tr>
				{foreach $addresses AS $key => $address}
					<tr {if $key %2}class="alt_row"{/if}>
						<td>{if $address['company']}{$address['company']}{else}--{/if}</td>
						<td>{$address['firstname']} {$address['lastname']}</td>
						<td>{$address['address1']} {if $address['address2']}{$address['address2']}{/if} {$address['postcode']} {$address['city']}</td>
						<td>{$address['country']}</td>
						<td class="right">
							{if $address['phone']}
								{$address['phone']}
								{if $address['phone_mobile']}<br />{$address['phone_mobile']}{/if}
							{else}
								{if $address['phone_mobile']}<br />{$address['phone_mobile']}{else}--{/if}
							{/if}
						</td>
						<td align="center">
							<a href="?tab=AdminAddresses&id_address={$address['id_address']}&addaddress&token={getAdminToken tab='AdminAddresses'}"><img src="../img/admin/edit.gif" /></a>
							<a href="?tab=AdminAddresses&id_address={$address['id_address']}&deleteaddress&token={getAdminToken tab='AdminAddresses'}"><img src="../img/admin/delete.gif" /></a>
						</td>
					</tr>
				{/foreach}
			</table>
		{else}
			{$customer->firstname} {$customer->lastname} {l s='has not registered any addresses yet' mod='germanext'}
		{/if}
	</div>
	
	<div style="float:left;width:50%">
		<div style="margin-left:15px">
			<h2>
				{l s='Groups' mod='germanext'} ({count($groups)})
				<a href="{$current}&updatecustomer&id_customer={$customer->id}&token={$token}">
					<img src="../img/admin/edit.gif" />
				</a>
			</h2>
			{if $groups AND count($groups)}
				<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
					<colgroup>
						<col width="10px">
						<col width="">
						<col width="70px">
					</colgroup>
					<tr>
						<th height="39px" class="left">{l s='ID' mod='germanext'}</th>
						<th class="left">{l s='Name' mod='germanext'}</th>
						<th class="center">{l s='Actions' mod='germanext'}</th>
					</tr>
				{foreach $groups AS $key => $group}
					<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '?tab=AdminGroups&id_group={$group['id_group']}&viewgroup&token={getAdminToken tab='AdminGroups'}'">
						<td class="left">{$group['id_group']}</td>
						<td class="left">{$group['name']}</td>
						<td class="center"><a href="?tab=AdminGroups&id_group={$group['id_group']}&viewgroup&token={getAdminToken tab='AdminGroups'}"><img src="../img/admin/details.gif" /></a></td>
					</tr>
				{/foreach}
				</table>
			{/if}
		</div>
	</div>

	<div class="clear">&nbsp;</div>
	
	{if ((isset($PS_PSTATISTIC) && $PS_PSTATISTIC == 1 && isset($customer->statistic) && $customer->statistic == 1) || (isset($GN_FORCE_STAT_GATHER) && $GN_FORCE_STAT_GATHER == 1))}
	
	{if count($interested)}
		<div>
		<h2>{l s='Products:' mod='germanext'} ({count($interested)})</h2>
			<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
				<colgroup>
					<col width="10px"></col>
					<col width=""></col>
					<col width="50px"></col>
				</colgroup>
				{foreach $interested as $key => $p}
					<tr {if $key %2}class="alt_row"{/if} style="cursor: pointer" onclick="document.location = '{$p['url']}'">
						<td>{$p['id']}</td>
						<td>{$p['name']}</td>
						<td align="center"><a href="{$p['url']}"><img src="../img/admin/details.gif" /></a></td>
					</tr>
				{/foreach}
			</table>
		</div>
	{/if}
	{/if}
				
	<div class="clear">&nbsp;</div>
	<div style="float:left;width:50%">
		{* Last connections *}
		{if ((isset($PS_PSTATISTIC) && $PS_PSTATISTIC == 1 && isset($customer->statistic) && $customer->statistic == 1) || (isset($GN_FORCE_STAT_GATHER) && $GN_FORCE_STAT_GATHER == 1)) && count($connections)}
			<h2>{l s='Last connections' mod='germanext'}</h2>
			<table cellspacing="0" cellpadding="0" class="table" style="width:100%;">
					<colgroup>
						<col width="150px"></col>
						<col width="100px"></col>
						<col width="100px"></col>
						<col width=""></col>
						<col width="150px"></col>
					</colgroup>
				<tr>
					<th height="39px;">{l s='Date' mod='germanext'}</th>
					<th>{l s='Pages viewed' mod='germanext'}</th>
					<th>{l s='Total time' mod='germanext'}</th>
					<th>{l s='Origin' mod='germanext'}</th>
					<th>{l s='IP Address' mod='germanext'}</th>
				</tr>
				{foreach $connections as $connection}
					<tr>
						<td>{$connection['date_add']}</td>
						<td>{$connection['pages']}</td>
						<td>{$connection['time']}</td>
						<td>{$connection['http_referer']}</td>
						<td>{$connection['ipaddress']}</td>
					</tr>
				{/foreach}
			</table>
			<div class="clear">&nbsp;</div>
		{/if}
	</div>
	
	<div style="float:left;width:50%">
		<div style="margin-left:15px">
			{if count($referrers)}
				<h2>{l s='Referrers' mod='germanext'}</h2>
				<table cellspacing="0" cellpadding="0" class="table">
					<tr>
						<th style="width: 200px">{l s='Date' mod='germanext'}</th>
						<th style="width: 200px">{l s='Name' mod='germanext'}</th>
						{if $shop_is_feature_active}<th style="width: 200px">{l s='Shop' mod='germanext'}</th>{/if}
					</tr>
					{foreach $referrers as $referrer}
						<tr>
							<td>{$referrer['date_add']}</td>
							<td>{$referrer['name']}</td>
							{if $shop_is_feature_active}<td>{$referrer['shop_name']}</td>{/if}
						</tr>
					{/foreach}
				</table>
			{/if}
		</div>
	</div>
{/block}
</div>	
<div class="clear">&nbsp;</div>

{*
 * Sensson WHMCS Incasso Module
 * Version 3.25
 *
 * All rights reserved, Sensson
 * E-mail: info@sensson.net
 *
 * Changes:
 * ----------------------------------
 * 20-02-2010
 * First release
 *
 * 01-11-2010
 * + added support for batch payments
 *
 * 02-11-2010
 * + added support for contract validation
 * - removed API settings, solved with internal WHMCS functions
 *
 * 18-04-2013
 * + added support for cancellations after the batch has been created
 *   as WHMCS removes products from an invoice the value changes
 *   we're tracking this now and will add credit if needed
 * + added links to the invoice numbers
 *
 * 14-7-2013
 * + proper support for the latest WHMCS version
 *
 * 18-8-2015
 * + Layout fixes for WHMCS 6
 * + Added a check so you can only create batches if invoices are selected.
 *
 * 29-3-2016
 * + A lot of layout changes to look more like the default WHMCS theme.
 **}

<style>
{literal}
/* Our default CSS */
.datepick-custom + img { position: relative; left: -21px; top: -1px; }
input[type="checkbox"] { margin: 1px; margin-right: 10px; }
.form-control { width: auto; }

/* Print */
@media print {
	/* Remove the following items from the page */
	#headerWrapper, #sidebar, #footer, #content_padded .nav,
	#content_padded .button-group, #content_padded .download-group,
	#content_padded #batch_manage, input {
		display: none;
	}

	/* Some minor adjustments */
	div.client-tabs > .active, div.admin-tabs > .active { border: 0px !important; }

	/* Override bootstrap */
	.col-md-10 { width: 100%; left: 0px; }
	a[href]:after { content: none !important; }
}
{/literal}
</style>
{if $ajax}
	{* check what to display *}
	{if $subscriptionchange}
		{if !$subscription_payment_selector_default}
			{if !$subscription_payment_selector_error}
				<select name="subscriptionvalue" class="form-control select-inline">
					{html_options options=$subscription_value selected=$subscription_value_default}
				</select>
			{/if}
			{if $subscription_payment_selector_error}
				This is not a dropdown custom field.&nbsp;
			{/if}
		{/if}
	{/if}

	{if $batchchange}

		<script>
		{literal}
		$("input[name='removefrombatch']").click(function(event) {
			var checkedBoxes = $("input[class='manage_batch']:checked").length;
			if(checkedBoxes == 0) {
				alert("You haven't selected any invoices.");
				event.preventDefault();
			}
		});
		$("input[name='processpayment']").click(function(event) {
			var checkedBoxes = $("input[class='manage_batch']:checked").length;
			if(checkedBoxes == 0) {
				alert("You haven't selected any invoices.");
				event.preventDefault();
			}
		});
		{/literal}
		</script>

		<form method="post" action="addonmodules.php?module=incasso" name="frm_managebatch">
		<input type="hidden" name="tab" id="tab" value="3">
		<h3>Direct debit batch #{$batch}</h3>
		<table>
			<thead>
				<tr>
					<td>&nbsp;</td>
					<td width="100"><strong>Invoice #</strong></td>
					<td width="100"><strong>Status</strong></td>
					<td width="250"><strong>Client Name</strong></td>
					<td width="100"><strong>Invoice Date</strong></td>
					<td width="100"><strong>Due Date</strong></td>
					<td width="120"><strong>Batch type</strong></td>
					<td width="200" align="right"><strong>Current Invoice Total</strong></td>
					{if $original_invoice_value eq 1}<td width="200" align="right"><strong>Batch Invoice Total</strong></td>{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$batchdata key=k item=i}
				{* Create a custom variable that we can use to set the name for a customer. *}
				{if $i.companyname|strlen !=0}
					{assign "name" "{$i.companyname} - {$i.firstname} {$i.lastname}"}
				{else}
					{assign "name" "{$i.firstname} {$i.lastname}"}
				{/if}
				<tr>
					<td>
						<input type="checkbox" name="chk_manage_{$i.id}_{$i.userid}_{math equation="x*100" x=$i.invoice_total}" class="manage_batch" />
					</td>
					<td><a href="invoices.php?action=edit&id={$i.id}">{if $i.invoicenum|strlen ==0}{$i.id}{else}{$i.invoicenum}{/if}</a></td>
					<td>{$i.status}</td>
					<td><a href="clientssummary.php?userid={$i.userid}">{$name|truncate:35:'..'}</a></td>
					<td>{$i.date|date_format:"%d-%m-%Y"}</td>
					<td>{$i.duedate|date_format:"%d-%m-%Y"}</td>
					<td>{if $i.batch_type == 0}Default: {if $i.batchType == 1}first{/if}{if $i.batchType == 2}recurring{/if}{/if}{if $i.batch_type == 1}first{/if}{if $i.batch_type == 2}recurring{/if}</td>
					<td align="right">&euro;{$i.total}</td>
					{if $original_invoice_value eq 1}<td align="right">&euro;{$i.invoice_total}</td>{/if}
				</tr>
			{/foreach}
			</tbody>
			<tfoot>
				<tr>
					<td align="right" colspan="7"><strong>Totals{if $original_invoice_value eq 1} *{/if}</strong></td>
					<td align="right"><strong>&euro;{$invoicetotal}</strong></td>
					{if $original_invoice_value eq 1}<td align="right"><strong>&euro;{$batchinvtotal}</strong></td>{/if}
				</tr>
			</tfoot>
		</table>

		{if $original_invoice_value eq 1}
		<p>* The current invoice total is the current invoice value. The batch invoice total is the value it held when the batch was created. This value will be used in the created batch file and payments. This is shown
		 as you selected the option use original invoice value.</p>
		 {/if}

			<script>
				{literal}
				$(function () { // this line makes sure this code runs on page load
					$('.checkallmanage').click(function () {
						$("input[class=manage_batch]").each(function() {
							if($(this).is(':checked') == true)
							{
								$(this).attr('checked', false);
							}
							else
							{
								$(this).attr('checked', true);
							}

						});
					});
				});
				{/literal}
			</script>

		<p class="button-group">
			<input type="button" value="Select All" class="button btn btn-default checkallmanage">
			<input type="submit" name="removefrombatch" class="button btn btn-warning" value="Remove from Batch" />
			<input type="submit" name="processpayment" class="button btn btn-success" value="Process Payment" />
		</p>
		</form>

		<div class="download-group">
			<h3>Download</h3>
			<p><input type="button" class="btn btn-default" onclick="window.location.href='{$incasso_batch_location}?hash={$securehash}&batch={$batch}&time={$incasso_unique_id}'" value="Download Complete Batch ({$invoicenumber} invoices)" />
			<input type="button" class="btn btn-default" onclick="window.location.href='{$incasso_batch_location}?hash={$securehash}&batch={$batch}&unpaid=true&time={$incasso_unique_id}'" value="Download Unpaid Batch ({$invoicenumberunpaid} invoices)" />
			<input type="button" class="btn btn-default" onclick="window.location.href='{$incasso_batch_location}?hash={$securehash}&batch={$batch}&batchType=1&time={$incasso_unique_id}'" value="Download batch as FRST" />
			<input type="button" class="btn btn-default" onclick="window.location.href='{$incasso_batch_location}?hash={$securehash}&batch={$batch}&batchType=2&time={$incasso_unique_id}'" value="Download batch as RCUR" />
			</p>

			<p>After uploading the file to your bank, you should verify that
			your bank shows a total amount of &euro;{$batchtotal} for {$invoicenumber} invoices.</p>

			{if $date_frst neq 0}
			<p>All invoices scheduled for their first direct debit will be or are processed on {$date_frst|date_format:"%d/%m/%Y"}.<br />
			All invoices scheduled for their recurring direct debit will be or are processed on {$date_rcur|date_format:"%d/%m/%Y"}.<br /></p>
			{/if}
		</div>
	{/if}
{/if}

{* {if !$ajax && $ready} *}
{if !$ajax}
<script>
	{literal}
	$(document).ready(function() {

		// Make sure the right tab is active
		var tab = '{/literal}{$selectedtab}{literal}';
		var tab_id = tab.match(/\d+/)[0]

		// Remove all classes from existing tabs and content
		$('.nav-tabs').children('li').each(function() {
    		$(this).removeClass('active');
		});
		$('.tab-content').children('div').each(function() {
			$(this).removeClass('active');
		});

		// Add a class
		$('#tabLink' + tab_id).closest('li').addClass('active');
		$('#tab' + tab_id).addClass('active');

		// Only continue if an invoice has been selected
		$("input[name='createbatch']").click(function(event) {
			var checkedBoxes = $("input[class='create_batch']:checked").length;
			if(checkedBoxes == 0) {
				alert("You haven't selected any invoices.");
				event.preventDefault();
			}
		});
	});

	function subscription_onchange(hash)
	{
		var today = new Date();
		var unixtime_ms = today.getTime();
		var unixtime = parseInt(unixtime_ms / 1000);

		var subscriptionmethod = $('#subscriptionmethod').val();
		{/literal}
		$.get('addonmodules.php?module=incasso&ajax=true&subscriptionchange=true&subscriptionmethod=' + subscriptionmethod + '&time=' + unixtime + '&hash=' + hash,
		{literal}
			function(data)
			{
				// insert the data in the proper div
				$('#span_subscriptionvalue').html(jQuery.trim(data));
			}
		);
	}

	function batch_onchange(hash)
	{
		var today = new Date();
		var unixtime_ms = today.getTime();
		var unixtime = parseInt(unixtime_ms / 1000);

		var batch = $('#batch').val();

		if(batch != 0)
		{
			{/literal}
			// $.get('../modules/addons/incasso/incasso.php?ajax=true&hash=' + hash + '&batchchange=true&batch=' + batch + '&time=' + unixtime,
			$.get('addonmodules.php?module=incasso&ajax=true&hash=' + hash + '&batchchange=true&batch=' + batch + '&time=' + unixtime,
			{literal}
				function(data)
				{
					// insert the data in the proper div
					$('#batch_content').html(jQuery.trim(data));
				}
			);
		}
		if(batch == 0)
		{
			$('#batch_content').html('');
		}
	}
	{/literal}
</script>

<div id="content_padded">

  {if $customer_error}
  <div class="errorbox">
    <strong>{$customer_error_subject}</strong><br />

    {foreach from=$customer_errors item=message}
      {$message}<br />
    {/foreach}

  </div>
  {/if}

	{if $formsubmit}
		{if $error}
		<div class="errorbox">
			<strong>{$infosubject}</strong><br />

			{foreach from=$errors item=message}
				{$message}<br />
			{/foreach}

		</div>
		{/if}
		{if !$error}
		<div class="successbox">
			<strong>{$infosubject}</strong><br />
			{$infostring}
		</div>
		{/if}
	{/if}

	<ul class="nav nav-tabs admin-tabs" role="tablist">
		<li class="active"><a href="#tab1" role="tab" data-toggle="tab" id="tabLink1">General</a></li>
		<li><a href="#tab2" role="tab" data-toggle="tab" id="tabLink2">Create Subscription Batch</a></li>
		<li><a href="#tab3" role="tab" data-toggle="tab" id="tabLink3">Manage Subscription Batch</a></li>
		<li><a href="#tab4" role="tab" data-toggle="tab" id="tabLink4">Subscription Settings</a></li>
	</ul>

	<div class="tab-content admin-tabs">
  		<div class="tab-pane active" id="tab1">
			<h3>Information</h3>
			<p>This version supports SEPA direct debits based on the pain.008.001.02 standard. More information on SEPA support is available on our website. Please visit the Sensson <a href="https://account.sensson.net/support/index.php?/Knowledgebase/List/Index/31/" target="_blank"><u>knowledgebase</u></a> for more general information on this module.</p>
			<p>You are currently running version {$current_version}. Check our <a href="https://account.sensson.net/support/index.php?/Knowledgebase/Article/View/88/" target="_blank"><u>release notes</u></a> to see if a new version is available.</p>

			<h3>Support</h3>
			<p>Is something not working as expected? Are you looking for a new feature? Feel free to contact support by <a href="https://account.sensson.net/support/index.php?/Tickets/Submit" target="_blank"><u>creating a ticket</u></a>.</p>
		</div>

		<div class="tab-pane" id="tab2">
  			<h3>Create subscription batch</h3>
  			{if $batch_invoices|@count gt 0}
  			<form method="post" action="addonmodules.php?module=incasso" name="frm_createbatch">
  			<input type="hidden" name="tab" id="tab" value="2">
  			<div id="createbatch">

  				<div id="date-selector">
  					<p>Set the dates when you want to run the batch. A FRST batch will take at least 7 days to run. A RCUR batch will take 3 days. If you leave it to the default settings it is recommended that you upload the batch today. You may encounter some issues if you don't.</p>
					<table>
						<tr>
							<td width="150">FRST batch date</td>
							<td>RCUR batch date</td>
						</tr>
						<tr>
							<td width="200">
								<input type="text" name="date-frst" value="{"+1 days"|date_format:"%d/%m/%Y"}" class="datepick-custom" id="dp-incasso-frst" readonly="true" style="background:white;">
							</td>
							<td width="200">
								<input type="text" name="date-rcur" value="{"+1 days"|date_format:"%d/%m/%Y"}" class="datepick-custom" id="dp-incasso-rcur" readonly="true" style="background:white;">
							</td>
						</tr>
					</table>
				</div>

				<h2>Unprocessed invoices</h2>
				<table>
					<thead>
						<tr>
							<td>&nbsp;</td>
							<td width="100"><strong>Invoice #</strong></td>
							<td width="250"><strong>Client Name</strong></td>
							<td width="100"><strong>Invoice Date</strong></td>
							<td width="100"><strong>Due Date</strong></td>
							<td width="250" style="padding-left: 5px;" align="left"><strong>Override batch type *</strong></td>
							<td width="100" align="right"><strong>Total</strong></td>
						</tr>
					</thead>
					<tbody>
					{foreach from=$batch_invoices key=k item=v}
						{* Create a custom variable that we can use to set the name for a customer. *}
						{if $v.companyname|strlen !=0}
							{assign "name" "{$v.companyname} - {$v.firstname} {$v.lastname}"}
						{else}
							{assign "name" "{$v.firstname} {$v.lastname}"}
						{/if}
							<tr>
								<td>
									<input type="checkbox" name="chk_create_{$v.id}_{$v.userid}_{math equation="x*100" x=$v.total}" class="create_batch" />
								</td>
								<td><a href="invoices.php?action=edit&id={$v.id}">{if $v.invoicenum|strlen ==0}{$v.id}{else}{$v.invoicenum}{/if}</a></td>
								<td><a href="clientssummary.php?userid={$v.userid}">{$name|truncate:35:'..'}</a></td>
								<td>{$v.date|date_format:"%d-%m-%Y"}</td>
								<td>{$v.duedate|date_format:"%d-%m-%Y"}</td>
								<td align="left" style="padding-left: 5px;">
									<select name="select_batchType_{$v.id}_{$v.userid}">
										<option value="0">Default</option>
										<option value="1">First</option>
										<option value="2">Recurring</option>
									</select> Default: {if $v.batchType == 1}first{/if}{if $v.batchType == 2}recurring{/if}
								</td>
								<td align="right">&euro;{$v.total}</td>
							</tr>
					{/foreach}
					</tbody>
				</table>
				<br />

				<script>
				{literal}
				$(function () { // this line makes sure this code runs on page load
					$('.checkallbatch').click(function () {
						$("input[class=create_batch]").each(function() {
							if($(this).is(':checked') == true) {
								$(this).attr('checked', false);
							}
							else {
								$(this).attr('checked', true);
							}

						});
					});

					$("#dp-incasso-frst").datepicker({
        				dateFormat: 'dd/mm/yy',
        				showOn: "both",
        				buttonImage: "images/showcalendar.gif",
        				buttonImageOnly: true,
        				showButtonPanel: true,
        				minDate: +1,
    				});

					$("#dp-incasso-rcur").datepicker({
        				dateFormat: 'dd/mm/yy',
        				showOn: "both",
        				buttonImage: "images/showcalendar.gif",
        				buttonImageOnly: true,
        				showButtonPanel: true,
        				minDate: +1,
    				});
				});
				{/literal}
				</script>

				<div id="incasso-buttons">
					<input type="button" value="Select All" class="button btn btn-default checkallbatch">
					<input type="submit" name="createbatch" class="button btn btn-success" value="Create Batch" />
					<br />&nbsp;
				</div>
				<p>Invoices will not be listed once they have been included in a batch payment. If you want to remove them, click the "Manage Subscription Batch" tab.</p>
				<p>* Please be careful and do not override the defaults if you don't need to as it may result in your bank denying the SEPA batch.</p>
			</div>
			</form>
			{else}
			<p>No unprocessed invoices found.</p>
			{/if}
		</div>

  		<div class="tab-pane" id="tab3">
  			<div id="batch_manage">
	  			<h3>Manage subscription batches</h3>
				<p>Please select a subscription batch:</p>
				<select id="batch" onchange="batch_onchange('{$securehash}');" class="form-control select-inline">
					<option value="0">--</option>
					{foreach from=$batches key=k item=v}
					<option value="{$k}">Batch: {$k} - Date: {$v.date}</option>
					{/foreach}
				</select>
			</div>

			<div id="batch_content"></div>

		</div>

		<!-- INCASSO SETTINGS -->
  		<div class="tab-pane" id="tab4">
  			<form method="post" action="addonmodules.php?module=incasso" name="configfrm">
  			<input type="hidden" name="tab" id="tab" value="4">
  			<p><b>General settings</b></p>
 			<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr>
					<td class="fieldlabel" width="20%">Your bank account</td>
					<td class="fieldarea"><input type="text" name="mybankaccount" value="{$mybankaccount}" size="30"> Your IBAN bank account number which is used to perform the subscription.</td>
				</tr>
				<tr>
					<td class="fieldlabel" width="20%">Your BIC code</td>
					<td class="fieldarea"><input type="text" name="mybic" value="{$mybic}" size="20" maxlength="20"> Your bank's BIC code for SEPA direct debit</td>
				</tr>
				<tr>
					<td class="fieldlabel">Your bank holder</td>
					<td class="fieldarea"><input type="text" name="mybankholder" value="{$mybankholder}" size="35" maxlength="35"> The owner of the bank account number set above (max. 35 characters)</td>
				</tr>
				<tr>
					<td class="fieldlabel">Your address</td>
					<td class="fieldarea"><input type="text" name="myaddress" value="{$myaddress}" size="35" maxlength="255"> Your address. This is used to generate a direct debit mandate.</td>
				</tr>
				<tr>
					<td class="fieldlabel">Your postcode</td>
					<td class="fieldarea"><input type="text" name="mypostcode" value="{$mypostcode}" size="35" maxlength="255"> Your postcode. This is used to generate a direct debit mandate.</td>
				</tr>
				<tr>
					<td class="fieldlabel">Your city</td>
					<td class="fieldarea"><input type="text" name="mycity" value="{$mycity}" size="35" maxlength="255"> Your city. This is used to generate a direct debit mandate.</td>
				</tr>
				<tr>
					<td class="fieldlabel">Your country</td>
					<td class="fieldarea"><select name="mycountry" class="form-control select-inline">
							{html_options options=$country_list selected=$country_list_default}
						</select> Your country. This is used to generate a direct debit mandate.</td>
				</tr>
				<tr>
					<td class="fieldlabel">Your identifier</td>
					<td class="fieldarea"><input type="text" name="myidentifier" value="{$myidentifier}" size="5" maxlength="5"> Identifier used to perform the incasso (max. 5 characters). Used as prefix with SEPA.</td>
				</tr>
				<tr>
					<td class="fieldlabel">Your fixed description</td>
					<td class="fieldarea"><input type="text" name="myfixeddesc" value="{$myfixeddesc}" size="32" maxlength="32"> Used to describe the incasso (max. 32 characters)</td>
				</tr>

				<tr>
					<td class="fieldlabel">Use original invoice value</td>
					<td class="fieldarea">
						<select name="original_invoice_value" class="form-control select-inline">
							{html_options options=$payment_options selected=$payment_option_default}
						</select> Use the original invoice value when creating batches and processing payments (default: no)
					</td>
				</tr>

				<tr>
					<td class="fieldlabel">Payment description prefix</td>
					<td class="fieldarea"><input type="text" name="payment_description_prefix" value="{$payment_description_prefix}" size="32" maxlength="32"> Add a prefix to the payment description (default: empty).</td>
				</tr>
			</table>
			<p><b>SEPA settings</b></p>
			<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<!-- SEPA -->
				<tr>
					<td class="fieldlabel">Enable SEPA</td>
					<td class="fieldarea">
						<select name="enable_sepa" class="form-control select-inline">
							{html_options options=$sepa_options selected=$payment_sepa_default}
						</select>
					</td>
				</tr>

				<tr>
					<td class="fieldlabel" width="20%">Creditor ID</td>
					<td class="fieldarea"><input type="text" name="mycreditorid" value="{$mycreditorid}" size="32" maxlength="32"> Creditor ID (this should be on your SEPA contract)</td>
				</tr>
				<tr>
					<td class="fieldlabel">Customer Mandate Reference</td>
					<td class="fieldarea">
						<select name="customer_manref" class="form-control select-inline">
							{html_options options=$subscription_options_manref selected=$subscription_manref_default}
						</select>
						A unique reference number for the customer's direct debit contract, set to use default if you have no specific reference
					</td>
				</tr>
				<tr>
					<td class="fieldlabel">Customer Mandate Sign Date</td>
					<td class="fieldarea">
						<select name="customer_mandate" class="form-control select-inline">
							{html_options options=$subscription_options selected=$subscription_mandate_default}
						</select>
						The date the customer signed for his/her direct debit contract, format: YYYY-MM-DD
					</td>
				</tr>
			</table>
			<p><b>Security</b></p>
			<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				<tr>
					<td class="fieldlabel" width="20%">API user</td>
					<td class="fieldarea">
						<select name="api_user" class="form-control select-inline">
							{html_options options=$api_users selected=$api_user}
						</select>
						Used to perform local API calls within WHMCS.
					</td>
				</tr>
				<tr>
					<td class="fieldlabel" width="20%">Secure Hash</td>
					<td class="fieldarea"><input type="text" name="securehash" value="{$securehash}" size="32"> Used to secure internal interactions with the module. Changing it is recommended.</td>
				</tr>
				{*
				<tr>
					<td class="fieldlabel">Contract Verification</td>
					<td class="fieldarea">
						<select name="contractvalidation" class="form-control select-inline">
							{html_options options=$contract_options selected=$contract_option_default}
						</select>
						This is an unsupported feature.
					</td>
				</tr>
				*}
			</table>
			<p><b>Configuration of custom fields for payment details</b></p>
			<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
				{if $nofieldsfound}
				<tr>
					<td colspan="2">You need to add some <a href="configcustomfields.php">custom fields</a> which are used to identify data from a client.</td>
				</tr>
				{/if}
				{if !$nofieldsfound}
				<tr>
					<td class="fieldlabel" width="20%">Subscription Field</td>
					<td class="fieldarea">
						<select name="subscriptionmethod" id="subscriptionmethod" onchange="subscription_onchange('{$securehash}');" class="form-control select-inline">
							{html_options options=$subscription_payment_selector selected=$subscription_field_default}
						</select>
						<span id="span_subscriptionvalue">
							{if !$subscription_payment_selector_default}
								{if !$subscription_payment_selector_error}
									<select name="subscriptionvalue" class="form-control select-inline">
										{html_options options=$subscription_value selected=$subscription_value_default}
									</select>
								{/if}
								{if $subscription_payment_selector_error}
									This is not a dropdown custom field.
								{/if}
							{/if}
						</span>
						&nbsp;Sets the subscription payment selector
					</td>
				</tr>
				<tr>
					<td class="fieldlabel">Client Bank Number</td>
					<td class="fieldarea">
						<select name="bankno" class="form-control select-inline">
							{html_options options=$subscription_options selected=$subscription_number_default}
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldlabel">Client BIC Number</td>
					<td class="fieldarea">
						<select name="bicno" class="form-control select-inline">
							{html_options options=$subscription_options selected=$subscription_bic_default}
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldlabel">Client Bank Holder</td>
					<td class="fieldarea">
						<select name="bankholder" class="form-control select-inline">
							{html_options options=$subscription_options selected=$subscription_holder_default}
						</select>
					</td>
				</tr>
				<tr>
					<td class="fieldlabel">Client Bank City</td>
					<td class="fieldarea">
						<select name="bankcity" class="form-control select-inline">
							{html_options options=$subscription_city_options selected=$subscription_city_default}
						</select>
					</td>
				</tr>
				{/if}
			</table>

			<p align="center"><input id="saveChanges" class="btn btn-primary" type="submit" value="Save Changes" name="submitform"></p>

		</div>

	</div>

	</form>
</div>

{/if}

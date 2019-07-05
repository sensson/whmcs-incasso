{extends "base.tpl"}
{block name=content}

{if $mandate eq false}
	{$language.no_mandate_found}
{else}
<style>
{literal}
/* Print */
@media print {
	/* Remove the following items from the page */
	#headerWrapper, #sidebar, #footer, #content_padded .nav, 
	#content_padded .button-group, #content_padded .download-group, 
	#content_padded #batch_manage, input, .btn-container {
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

<div class="tab-content admin-tabs">
	<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
		<tbody>
			<tr>
				<td class="fieldlabel" colspan="1" width="230"><strong>{$language.directdebit_name}</strong></td>
				<td class="fieldarea" colspan="5">{$mandate->company_name}</td>
			</tr>
			<tr>
				<td class="fieldlabel" colspan="1"><strong>{$language.directdebit_address}</strong></td>
				<td class="fieldarea" colspan="5">{$mandate->company_address}</td>
			</tr>
			<tr>
				<td class="fieldlabel"><strong>{$language.directdebit_postcode}</strong></td>
				<td class="fieldarea">{$mandate->company_postcode}</td>
			</tr>
			<tr>
				<td class="fieldlabel"><strong>{$language.directdebit_city}</strong></td>
				<td class="fieldarea">{$mandate->company_city}</td>
			</tr>
			<tr>
				<td class="fieldlabel"><strong>{$language.directdebit_country}</strong></td>
				<td class="fieldarea">{$mandate->company_country}</td>
			</tr>
			<tr>
				<td class="fieldlabel" colspan="1"><strong>{$language.directdebit_creditorid}</strong></td>
				<td class="fieldarea" colspan="5">{$mandate->company_creditor_id}</td>
			</tr>
			<tr>
				<td class="fieldlabel" colspan="1"><strong>{$language.directdebit_mandate_reference}</strong></td>
				<td class="fieldarea" colspan="5">{$mandate->mandate_reference}</td>
			</tr>
			<tr>
				<td class="fieldlabel" colspan="1"><strong>{$language.directdebit_mandate_reason}</strong></td>
				<td class="fieldarea" colspan="5">{$mandate->mandate_description}</td>
			</tr>
		</tbody>
	</table>
</div>

<div class="table-sepa-information">
	<p>{$language.directdebit_sign_first} <strong>{$mandate->company_name}</strong> {$language.directdebit_sign_second} <strong>{$mandate->company_name}</strong>.</p>
	<p>{$language.directdebit_sign_third}</p>
</div>

<div class="tab-content admin-tabs">
	<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
		<tbody>
			<tr>
				<td class="fieldlabel" colspan="1" width="230"><strong>{$language.directdebit_name}</strong></td>
				<td class="fieldarea" colspan="5">{$mandate->customer_name}</td>
			</tr>
			<tr>
				<td class="fieldlabel" colspan="1"><strong>{$language.directdebit_address}</strong></td>
				<td class="fieldarea" colspan="5">{$mandate->customer_address}</td>
			</tr>
			<tr>
				<td class="fieldlabel"><strong>{$language.directdebit_postcode}</strong></td>
				<td class="fieldarea">{$mandate->customer_postcode}</td>
			</tr>
				<td class="fieldlabel"><strong>{$language.directdebit_city}</strong></td>
				<td class="fieldarea">{$mandate->customer_city}</td>
			<tr>
				<td class="fieldlabel"><strong>{$language.directdebit_country}</strong></td>
				<td class="fieldarea">{$mandate->customer_country}</td>
			</tr>
			<tr>
				<td class="fieldlabel" colspan="1"><strong>IBAN</strong></td>
				<td class="fieldarea" colspan="5">{$mandate->customer_bankaccount_number}</td>
			</tr>
			<tr>
				<td class="fieldlabel" colspan="1"><strong>BIC</strong></td>
				<td class="fieldarea" colspan="5">{$mandate->customer_bankaccount_bic}</td>
			</tr>
			<tr>
				<td class="fieldlabel" colspan="1"><strong>{$language.directdebit_city_date}</strong></td>
				<td class="fieldarea" colspan="5">{$mandate->customer_bankaccount_city}, {$mandate->customer_signature_date|date_format:"%d/%m/%Y"}</td>
			</tr>
		</tbody>
	</table>

	<p align="center"><img src="{$mandate->customer_signature}" /></p>

	<p>{$language.signed_by_ip}<br />{$mandate->ipaddress} {$language.signed_at} {$mandate->timestamp|date_format:"%d/%m/%Y, %H:%M"}.</p>
</div>
{/if}

<div class="btn-container">
	<a href="{$modulelink}&amp;page=mandates&amp;view=list" class="btn btn-default">{$language.go_back}</a>
</div>


{/block}

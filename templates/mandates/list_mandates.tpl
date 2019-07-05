{extends "base.tpl"}
{block name=content}

	<!-- Reuse existing search functionality -->
	<script>function getClientSearchPostUrl() { return '/oam/admin/orders.php'; }</script>
	<script type="text/javascript" src="../assets/js/AdminClientDropdown.js"></script>

	<h3>{$language.mandates}</h3>

	{block name=introduction}
		<p>{$language.introduction}</p>
	{/block}
	{block name=search}{/block}

	{block name="list"}
	<div class="tablebg">

		<table id="sortabletbl1" class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
			<thead>
				
			</thead>
			<tbody>
				<tr>
					<th>{$language.uid}</th>
					<th>{$language.directdebit_mandate_reference}</th>
					<th>{$language.directdebit_mandate_clientid}</th>
					<th>{$language.client_name}</th>
					<th>{$language.signing_date}</th>
					<th>&nbsp;</th>
				</tr>

			{foreach from=$mandates item=mandate}
				<tr>
					{assign "fullname" "{$mandate->customer_name}"}
					<td><a href='{$modulelink}&amp;page=mandates&amp;action=view&amp;id={$mandate->uid}'>{$mandate->uid}</a></td>
					<td><a href='{$modulelink}&amp;page=mandates&amp;action=view&amp;id={$mandate->uid}'>{$mandate->mandate_reference}</a></td>
					<td><a href='clientssummary.php?userid={$mandate->customer_id}'>{$mandate->customer_id}</a></td>
					<td><a href='clientssummary.php?userid={$mandate->customer_id}'>{$fullname|truncate:35:'..'}</a></td>
					<td>{$mandate->timestamp|date_format:"%d/%m/%Y"}</td>
					<td><a href='{$modulelink}&amp;page=mandates&amp;action=view&amp;id={$mandate->uid}'>{$language.view_mandate}</a></td>
				</tr>
			{/foreach}
			
			</tbody>
		</table>

		<p>{count($mandates)} {$language.records_found}.</p>

	</div>
	{/block}

{/block}

{block name=search}
<div class="tab-content admin-tabs">
	<form action="{$modulelink}&amp;page=mandates" method="post">
		<table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
			<tr>
				<td width="15%" class="fieldlabel">{$language.directdebit_mandate_reference}</td>
				<td width="35%" class="fieldarea">
					<input type="text" name="mandate_reference" class="form-control input-150" value="{$mandate_reference}">
				</td>

				<td width="15%" class="fieldlabel">{$language.client_name}</td>
				<td width="35%" class="fieldarea">
					<select id="selectClientid" name="customer_id" class="form-control selectize selectize-client-search" placeholder="{$language.clientsearch_placeholder}" data-value-field="id">
						{if $client}
						<option value="1" selected="selected">Thomas Klaver</option>
						{/if}
					</select>
				</td>
			</tr>
		</table>

		<div class="btn-container">
			<input type="submit" value="{$language.search}" class="btn btn-default" name="submitfilter">
			{if $filtered}<a href="{$modulelink}&amp;page=mandates" class="btn btn-default" name="clearfilter">{$language.clear}</a>{/if}
		</div>
	</form>
</div>
{/block}

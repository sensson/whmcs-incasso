<!-- Content block -->
{include file="$template/includes/tablelist.tpl" tableName="MandatesList" filterColumn="2"}
 <script type="text/javascript">
	jQuery(document).ready( function ()
	{
		var table = jQuery('#tableMandatesList').removeClass('hidden').DataTable();
		table.draw();
		jQuery('#tableLoading').addClass('hidden');
	});
</script>

<div class="table-container clearfix">
	<table id="tableMandatesList" class="table table-list hidden">
		<thead>
			<tr>
				<th>{$addon_lang.directdebit_mandate_reference}</th>
				<th>{$addon_lang.signing_date}</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>

		{foreach from=$mandates item=mandate}
			<tr onclick="clickableSafeRedirect(event, '{$modulelink}&amp;page=mandates&amp;action=view&amp;id={$mandate->uid}', false)">
				<td><a href='{$modulelink}&amp;page=mandates&amp;action=view&amp;id={$mandate->uid}'>{$mandate->mandate_reference}</a></td>
				<td>{$mandate->timestamp|date_format:"%d/%m/%Y"}</td>
				<td><a href='{$modulelink}&amp;page=mandates&amp;action=view&amp;id={$mandate->uid}'>{$addon_lang.view_mandate}</a></td>
			</tr>
		{/foreach}

		</tbody>
	</table>

	<div class="text-center" id="tableLoading">
		<p><i class="fa fa-spinner fa-spin"></i> {$LANG.loading}</p>
	</div>
</div>
<!-- /Content block -->
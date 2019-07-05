{if $payment_link}
<div class="incasso-link unpaid">
	<p>{$language.directdebit_no_mandate} <a href="{$systemurl|rtrim:'/'}/index.php?m=incasso&amp;action=sign">{$language.directdebit_mandate}</a>.</p>
</div>
{else}
{$language.directdebit_invoice_process} {$mandate->customer_bankaccount_number}
{/if}


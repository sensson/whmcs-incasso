{capture assign='customfield_bankholder'}customfields{$settings.bankholder}{/capture}
{capture assign='customfield_bankiban'}customfields{$settings.bankno}{/capture}
{capture assign='customfield_bankbic'}customfields{$settings.bankbic}{/capture}
{capture assign='customfield_signing_date'}customfields{$settings.customer_mandate}{/capture}
{capture assign='customfield_mandate_reference'}customfields{$settings.customer_manref}{/capture}

{if $errors}
	{include file="$template/includes/alert.tpl" type="error" msg=$addon_lang.sign_mandate_failed textcenter=true}
{/if}

{if (!$display_form)}
	<p>{$addon_lang.valid_mandate_exists}</p>
{else}
	<style>
	#directdebit_mandate .align-center { text-align: center; }
	#directdebit_mandate .align-left { text-align: left; }
	#directdebit_mandate .align-right { text-align: right; }
	#directdebit_mandate td { vertical-align: middle; }
	#directdebit_mandate td canvas { border-bottom: 1px solid grey; }
	#directdebit_mandate td input[type='text'].input-error { border: 1px red solid; }

	input[name='customer_name'],
	input[name='customer_bankaccount_number'],
	input[name='customer_bankaccount_bic'],
	input[name='customer_bankaccount_city'] {
		width: 50% !important;
	}

	.tooltip-inner {
		max-width: 320px !important;
	}

	@media (max-width: 320px) {
		.tooltip-inner {
			min-width: initial;
			width: 320px;
		}
	}

	</style>

	<div id="directdebit_mandate" class="align-left">
		<div>
			<form method="post" id="signatureForm">
				<input type="hidden" name="signature" value="" />
				<input type="hidden" name="customer_signature_date" value="{$customer_signature_date}" />
				<input type="hidden" name="mandate_reference" value="{$mandate_reference}" />

				<p>{$addon_lang.directdebit_intro}</p>

				<div class="table-responsive">
					<table class="table table-condensed">
						<tbody>
							<tr>
								<td colspan="1" width="230"><strong>{$addon_lang.directdebit_name}</strong></td>
								<td colspan="5">{$settings.mybankholder}</td>
							</tr>
							<tr>
								<td colspan="1"><strong>{$addon_lang.directdebit_address}</strong></td>
								<td colspan="5">{$settings.myaddress}</td>
							</tr>
							<tr>
								<td><strong>{$addon_lang.directdebit_postcode}</strong></td>
								<td>{$settings.mypostcode}</td>
								<td><strong>{$addon_lang.directdebit_city}</strong></td>
								<td>{$settings.mycity}</td>
								<td><strong>{$addon_lang.directdebit_country}</strong></td>
								<td>{$settings.mycountry}</td>
							</tr>
							<tr>
								<td colspan="1"><strong>{$addon_lang.directdebit_creditorid}</strong></td>
								<td colspan="5">{$settings.mycreditorid}</td>
							</tr>
							<tr>
								<td colspan="1"><strong>{$addon_lang.directdebit_mandate_reference}</strong></td>
								<td colspan="5">{$mandate_reference}</td>
							</tr>
							<tr>
								<td colspan="1"><strong>{$addon_lang.directdebit_mandate_reason}</strong></td>
								<td colspan="5">{$settings.myfixeddesc}</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="table-sepa-information">
					<p>{$addon_lang.directdebit_sign_first} <br /><strong>{$settings.mybankholder}</strong></p>
					<p>{$addon_lang.directdebit_sign_second} <br /><strong>{$settings.mybankholder}</strong></p>
					<p>{$addon_lang.directdebit_sign_third}</p>
				</div>

				<div class="table-responsive">
					<table class="table table-condensed">
						<tbody>
							<tr>
								<td colspan="1" width="230"><strong>{$addon_lang.directdebit_name}</strong></td>
								<td colspan="5"><input class="{if $errors.customer_name}input-error{/if}" type="text" name="customer_name" value="{$customer_name}" /> {$errors.customer_name}</td>
							</tr>
							<tr>
								<td colspan="1"><strong>{$addon_lang.directdebit_address}</strong></td>
								<td colspan="5">{$clientsdetails.address1}</td>
							</tr>
							<tr>
								<td><strong>{$addon_lang.directdebit_postcode}</strong></td>
								<td>{$clientsdetails.postcode}</td>
								<td><strong>{$addon_lang.directdebit_city}</strong></td>
								<td>{$clientsdetails.city}</td>
								<td><strong>{$addon_lang.directdebit_country}</strong></td>
								<td>{$clientsdetails.country}</td>
							</tr>
							<tr>
								<td colspan="1"><strong>IBAN</strong></td>
								<td colspan="5">
									<input class="{if $errors.customer_bankaccount_number}input-error{/if}" type="text" name="customer_bankaccount_number" value="{$customer_bankaccount_number}" /> {$errors.customer_bankaccount_number}
								</td>
							</tr>
							<tr>
								<td colspan="1"><strong>BIC</strong></td>
								<td colspan="5">
									<input class="{if $errors.customer_bankaccount_bic}input-error{/if}" type="text" name="customer_bankaccount_bic" value="{$customer_bankaccount_bic}" />
									{$errors.customer_bankaccount_bic}
									<span class="fa fa-question-circle" data-toggle="tooltip" title="{$addon_lang.help_bic}"></span>
								</td>
							</tr>
							<tr>
								<td colspan="1"><strong>{$addon_lang.directdebit_city_date}</strong></td>
								<td colspan="5">{if $settings.bankcity != 9999}<input class="{if $errors.customer_bankaccount_city}input-error{/if}" type="text" name="customer_bankaccount_city" value="{$customer_bankaccount_city}" /> {$errors.customer_bankaccount_city}{else}{$clientsdetails.city}{/if}, {$customer_signature_date|date_format:"%d/%m/%Y"}</td>
							</tr>
							<tr>
								<td colspan="6" class="align-left">
									<p><strong>{$addon_lang.directdebit_signature}</strong></p>
									<canvas></canvas>
									<p class="small">{$addon_lang.help_signature}</p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="default-payment-method align-center">
					<p align="center">
						<label class="checkbox-inline">
							<input type="checkbox" name="change_default_payment_method" id="defaultpayment" {if $change_default_payment_method}checked{/if} />
							&nbsp;
							{$addon_lang.directdebit_default_payment_method} <span class="fa fa-question-circle" data-toggle="tooltip" title="{$addon_lang.help_default_payment_method}"></span>
						</label>
					</p>
				</div>

				<div class="m-signature-pad--footer">
					<div class="form-group align-center">
						<input type="button" value="{$addon_lang.directdebit_clear}" data-action="clear" class="button btn btn-default checkallbatch">
						<input type="submit" value="{$addon_lang.directdebit_sign}" data-action="save-direct-debit" class="button btn btn-success" />
					</div>
				</div>

				<script type="text/javascript" src="{$systemurl}modules/addons/incasso/templates/js/signature_pad/signature_pad.min.js"></script>
				<script type="text/javascript">
				{literal}
				var canvas = document.querySelector("canvas"),
					clearButton = document.querySelector("[data-action=clear]"),
					signature = document.querySelector("input[name=signature]"),
					signatureForm = document.querySelector("#signatureForm"),
					signaturePad;

				var signaturePad = new SignaturePad(canvas, {
					minWidth: 1,
					maxWidth: 1,
				});

				// Backwards compatibility with older browsers
				if (document.addEventListener) {
					clearButton.addEventListener("click", function (event) {
						signaturePad.clear();
					});

					signatureForm.addEventListener("submit", function (event) {
						if (signaturePad.isEmpty()) {
							// Send an alert if the signature is empty and do not submit the form
							alert("{/literal}{$addon_lang.directdebit_sign_failed}{literal}");
							event.preventDefault();
							return false;
						} else {
							// Set the signature as a form field so we can save it
							signature.value = signaturePad.toDataURL();
						}
					});
				} else if (document.attachEvent) {
					clearButton.attachEvent("click", function (event) {
						signaturePad.clear();
					});

					signatureForm.attachEvent("submit", function (event) {
						if (signaturePad.isEmpty()) {
							// Send an alert if the signature is empty and do not submit the form
							alert("{/literal}{$addon_lang.directdebit_sign_failed}{literal}");
							event.preventDefault();
							return false;
						} else {
							// Set the signature as a form field so we can save it
							signature.value = signaturePad.toDataURL();
						}
					});
				}
				{/literal}
				</script>
			</form>
		</div>
	</div>
{/if}

<?php
$_ADDONLANG['scheduled_direct_debits'] = 'Automatische incasso';
$_ADDONLANG['scheduled_on'] = 'wordt ge&#xEF;ncasseerd op';
$_ADDONLANG['total_scheduled'] = 'Het totaal bedrag wat geïncasseerd zal worden is';

// Direct debit authorization form
$_ADDONLANG['directdebit_mandate'] = 'Incasso Machtiging';
$_ADDONLANG['directdebit_no_mandate'] = 'Wij kunnen deze factuur nog niet verwerken. Bekijk de';
$_ADDONLANG['directdebit_authorization'] = 'Doorlopende machtiging';
$_ADDONLANG['directdebit_intro'] = 'U kunt ons machtigen om uw betalingen te verwerken middels automatische incasso. Wij hebben nog geen volledige machtiging van u ontvangen. Met dit formulier kunt u ons machtigen om in de toekomst betalingen per automatische incasso uit te laten voeren.';
$_ADDONLANG['directdebit_name'] = 'Naam';
$_ADDONLANG['directdebit_address'] = 'Adres';
$_ADDONLANG['directdebit_postcode'] = 'Postcode';
$_ADDONLANG['directdebit_city'] = 'Plaats';
$_ADDONLANG['directdebit_country'] = 'Land';
$_ADDONLANG['directdebit_creditorid'] = 'Incassant ID';
$_ADDONLANG['directdebit_mandate_reference'] = 'Kenmerk machtiging';
$_ADDONLANG['directdebit_mandate_clientid'] = 'Klant nummer';
$_ADDONLANG['directdebit_mandate_reason'] = 'Reden betaling';
$_ADDONLANG['directdebit_sign_first'] = 'Door ondertekening van dit formulier geeft u toestemming aan';
$_ADDONLANG['directdebit_sign_second'] = 'om doorlopende incasso-opdrachten te sturen naar uw bank om een bedrag van uw rekening af te schrijven en aan uw bank om doorlopend een bedrag van uw rekening af te schrijven overeenkomstig de opracht van';
$_ADDONLANG['directdebit_sign_third'] = 'Als u het niet eens bent met deze afschrijving kunt u deze laten terugboeken. Neem hiervoor binnen 8 weken na afschrijving contact op met uw bank. Vraag uw bank naar de voorwaarden.';
$_ADDONLANG['directdebit_city_date'] = 'Plaats en datum';
$_ADDONLANG['directdebit_signature'] = 'Handtekening';
$_ADDONLANG['directdebit_close'] = 'Sluit dit venster en bekijk factuur';
$_ADDONLANG['directdebit_clear'] = 'Verwijder handtekening';
$_ADDONLANG['directdebit_sign'] = 'Onderteken machtiging';
$_ADDONLANG['directdebit_sign_failed'] = 'U heeft de machtiging nog niet ondertekend.';
$_ADDONLANG['directdebit_invoice_process'] = 'Deze factuur zal automatisch worden geïncasseerd van uw rekening nummer';
$_ADDONLANG['directdebit_default_payment_method'] = 'Wijzig mijn standaard betaalmethode naar automatische incasso';

// Admin area
$_ADDONLANG['modulename'] = 'Incasso';

// Mandate index
$_ADDONLANG['general'] = 'Algemeen';
$_ADDONLANG['mandates'] = 'Mandaten';
$_ADDONLANG['create'] = 'Maak een nieuwe incasso batch';
$_ADDONLANG['manage'] = 'Beheer incasso batches';
$_ADDONLANG['settings'] = 'Instellingen';

$_ADDONLANG['uid'] = 'UID';
$_ADDONLANG['client_name'] = 'Klant naam';
$_ADDONLANG['view_mandate'] = 'Bekijk mandaat';
$_ADDONLANG['signing_date'] = 'Datum ondertekening';
$_ADDONLANG['introduction'] = 'Bekijk ondertekende mandaten van uw klanten. Het is niet mogelijk om deze mandaten te wijzen. Als een mandaat verkeerde informatie bevat moet deze opnieuw worden ondertekend.';
$_ADDONLANG['records_found'] = 'resultaten gevonden';
$_ADDONLANG['search'] = 'Zoek';
$_ADDONLANG['clear'] = 'Wis filter';
$_ADDONLANG['clientsearch_placeholder'] = 'Start Typing to Search Clients';

// View mandate
$_ADDONLANG['go_back'] = 'Ga terug';
$_ADDONLANG['signed_by_ip'] = 'Dit formulier was ondertekend door het volgende IP adres:';
$_ADDONLANG['signed_at'] = 'op';
$_ADDONLANG['no_mandate_found'] = 'Dit mandaat bestaat niet.';

// Client area
$_ADDONLANG['direct_debit'] = 'Automatische incasso';
$_ADDONLANG['menu_list'] = 'Overzicht';
$_ADDONLANG['menu_sign'] = 'Online machtiging';
$_ADDONLANG['valid_mandate_exists'] = 'Er bestaat een geldige incasso machtiging voor uw account. Neem contact met ons op om een bestaand mandaat te wijzigen of te annuleren.';
$_ADDONLANG['valid_mandate_saved'] = 'Uw online machtiging is opgeslagen. Bekijk uw <a href="%s">machtiging</a>.';
$_ADDONLANG['help_signature'] = 'Gebruik uw muis om hierboven een handtekening te zetten';
$_ADDONLANG['sign_mandate_failed'] = 'Het ondertekenen van het mandaat is niet gelukt. Neem contact met ons op voor meer informatie.';

// Help
$_ADDONLANG['help_bic'] = 'Bekende BIC codes voor banken in Nederland zijn onder andere ABNANL2A, RABONL2U en INGBNL2A';
$_ADDONLANG['help_default_payment_method'] = 'Dit wijzigt de standaard betaalmethode voor uw hele account en alle actieve services naar automatische incasso.';

if (file_exists(__DIR__ . '/overrides/dutch.php')) {
  require __DIR__ . '/overrides/dutch.php';
}

?>

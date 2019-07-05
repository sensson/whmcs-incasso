<?php
$_ADDONLANG['scheduled_direct_debits'] = 'Direct debit';
$_ADDONLANG['scheduled_on'] = 'is scheduled for direct debit on';
$_ADDONLANG['total_scheduled'] = 'The total amount scheduled for direct debit is';

// Direct debit authorization form
$_ADDONLANG['directdebit_mandate'] = 'Direct Debit Mandate';
$_ADDONLANG['directdebit_no_mandate'] = 'We cannot process this invoice just yet. Please open the';
$_ADDONLANG['directdebit_authorization'] = 'Recurring collections';
$_ADDONLANG['directdebit_intro'] = 'We can process your invoices via direct debit. We have not yet received a filled in mandate form in order to process your payments. With this form you can authorize us to process your invoices via direct debit in the future.';
$_ADDONLANG['directdebit_name'] = 'Name';
$_ADDONLANG['directdebit_address'] = 'Address';
$_ADDONLANG['directdebit_postcode'] = 'Postcode';
$_ADDONLANG['directdebit_city'] = 'City';
$_ADDONLANG['directdebit_country'] = 'Country';
$_ADDONLANG['directdebit_creditorid'] = 'Creditor ID';
$_ADDONLANG['directdebit_mandate_reference'] = 'Mandate reference';
$_ADDONLANG['directdebit_mandate_clientid'] = 'Client ID';
$_ADDONLANG['directdebit_mandate_reason'] = 'Reason for payment';
$_ADDONLANG['directdebit_sign_first'] = 'By signing this mandate form, you authorize';
$_ADDONLANG['directdebit_sign_second'] = 'to send recurring collection instructions to your bank to debit your account and you authorize your bank to debit your account on a recurrent basis in accordance with the instructions from';
$_ADDONLANG['directdebit_sign_third'] = 'If you disagree with this transaction you are entitled to a refund. Please contact your bank within 8 weeks. Ask your bank for the terms and conditions.';
$_ADDONLANG['directdebit_city_date'] = 'City and date';
$_ADDONLANG['directdebit_signature'] = 'Signature';
$_ADDONLANG['directdebit_close'] = 'Close this window and view invoice';
$_ADDONLANG['directdebit_clear'] = 'Clear signature';
$_ADDONLANG['directdebit_sign'] = 'Sign mandate';
$_ADDONLANG['directdebit_sign_failed'] = 'You haven\'t signed the mandate form yet.';
$_ADDONLANG['directdebit_invoice_process'] = 'This invoice will be collected using direct debit from your bank account with the number';
$_ADDONLANG['directdebit_default_payment_method'] = 'Change my default payment method to Direct Debit';


// Admin area
$_ADDONLANG['modulename'] = 'Incasso';

// Mandate index
$_ADDONLANG['general'] = 'General';
$_ADDONLANG['mandates'] = 'Mandates';
$_ADDONLANG['create'] = 'Create Subcription Batch';
$_ADDONLANG['manage'] = 'Manage Subcription Batch';
$_ADDONLANG['settings'] = 'Subscription Settings';

$_ADDONLANG['uid'] = 'UID';
$_ADDONLANG['client_name'] = 'Client Name';
$_ADDONLANG['view_mandate'] = 'View mandate';
$_ADDONLANG['signing_date'] = 'Signing date';
$_ADDONLANG['introduction'] = 'View signed mandates of your customers. It is not possible to modify mandates. If a mandate is wrong or needs to be changed a new mandate should be signed.';
$_ADDONLANG['records_found'] = 'records found';
$_ADDONLANG['search'] = 'Search';
$_ADDONLANG['clear'] = 'Clear filter';
$_ADDONLANG['clientsearch_placeholder'] = 'Start Typing to Search Clients';

// View mandate
$_ADDONLANG['go_back'] = 'Go back';
$_ADDONLANG['signed_by_ip'] = 'This form was signed by the following IP address:';
$_ADDONLANG['signed_at'] = 'at';
$_ADDONLANG['no_mandate_found'] = 'This mandate doesn\'t exist.';

// Client area
$_ADDONLANG['direct_debit'] = 'Direct debit';
$_ADDONLANG['menu_list'] = 'Overview';
$_ADDONLANG['menu_sign'] = 'Online mandate';
$_ADDONLANG['valid_mandate_exists'] = 'A valid mandate exists for your account. Please contact us if you would like to change or cancel a mandate.';
$_ADDONLANG['help_signature'] = 'Use your mouse to sign this document in the field above';
$_ADDONLANG['sign_mandate_failed'] = 'We weren\'t able to process your form. Please get in touch with us for more details.';

// Help
$_ADDONLANG['help_bic'] = 'A BIC code is used to uniquely identify banks and financial institutions.';
$_ADDONLANG['help_default_payment_method'] = 'This will change the default payment method for your account and all of your existing services to direct debit.';

if (file_exists(__DIR__ . '/overrides/english.php')) {
  require __DIR__ . '/overrides/english.php';
}

?>

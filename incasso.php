<?php
/**
 * Incasso direct debit module
 *
 * This module allows direct debits based on the SEPA standard. It's only requirement
 * is that you have a contract with your bank to perform direct debits.
 *
 * Support is available via info@sensson.net.
 *
 * @author      Sensson <info@sensson.net>
 * @copyright   2004-2016 Sensson
 * @license     This software is furnished under a license and may be used and copied
 *              only  in  accordance  with  the  terms  of such  license and with the
 *              inclusion of the above copyright notice.  This software  or any other
 *              copies thereof may not be provided or otherwise made available to any
 *              other person.  No title to and  ownership of the  software is  hereby
 *              transferred.
 *
 */

// Oversimplified WHMCS security
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$loader = new Composer\Autoload\ClassLoader();
$loader->addPsr4('Incasso\\', __DIR__ . '/lib/incasso');
$loader->addPsr4('IsoCodes\\', __DIR__ . '/lib/isocodes');
$loader->register();

use Illuminate\Database\Capsule\Manager as Capsule;

use Incasso\Models\Mandate;
use Incasso\Models\Setting;
use Incasso\Core\Helper;
use Incasso\Validators\MandateValidator;

// Include our classes
include("class/isocodes/IsoCodeInterface.php");
include("class/isocodes/Iban.php");
include("class/isocodes/SwiftBic.php");
include("class/incasso.class.php");

/**
 * Set the incasso configuration
 *
 * @param none
 * @return array
 */
function incasso_config() {
    $config = [
        "name"          => "Incasso / SEPA direct debit",
        "description"   => "This module is used to perform direct debits based on the SEPA standard.",
        "version"       => "4.35",
        "author"        => "Sensson",
        "language"      => "english"
    ];

    return $config;
}

/**
 * Activation of the incasso module
 *
 * @param none
 * @return array
 */
function incasso_activate() {
    // Check if the table exissts
    if(Capsule::schema()->hasTable('mod_incasso') and Capsule::schema()->hasTable('mod_incasso_batch') and Capsule::schema()->hasTable('mod_incasso_mandates')) {
        return array(
            'status' => 'success',
            'description' => 'The incasso module has been installed already and is activated.'
        );
    }

    try {
        // Create table: mod_incasso
        Capsule::schema()->create(
            'mod_incasso',
            function($table) {
                $table->increments('configid');
                $table->string('configname');
                $table->string('configvalue');
            }
        );

        // Create table: mod_incasso_batch
        Capsule::schema()->create(
            'mod_incasso_batch',
            function($table) {
                $table->increments('uid');
                $table->integer('batch_id');
                $table->integer('invoice_id');
                $table->integer('batch_type');
                $table->decimal('invoice_total', 10, 2);
                $table->integer('client_id');
                $table->integer('date');
                $table->integer('date_frst');
                $table->integer('date_rcur');
            }
        );

        Capsule::schema()->create(
            'mod_incasso_mandates',
            function($table) {
                $table->increments('uid');
                $table->string('timestamp');
                $table->string('ipaddress');
                $table->string('company_name');
                $table->string('company_address');
                $table->string('company_postcode');
                $table->string('company_city');
                $table->string('company_country');
                $table->string('company_creditor_id');
                $table->string('mandate_reference');
                $table->string('mandate_description');
                $table->integer('customer_id');
                $table->string('customer_name');
                $table->string('customer_address');
                $table->string('customer_postcode');
                $table->string('customer_city');
                $table->string('customer_country');
                $table->string('customer_bankaccount_number');
                $table->string('customer_bankaccount_bic');
                $table->string('customer_bankaccount_city');
                $table->text('customer_signature');
                $table->text('customer_signature_date');
            }
        );

        // Insert default settings: mod_incasso
        Capsule::table('mod_incasso')->insert([
            [ 'configname' => 'apiurl', 'configvalue' => '' ],
            [ 'configname' => 'apiusername', 'configvalue' => '' ],
            [ 'configname' => 'apipassword', 'configvalue' => '' ],
            [ 'configname' => 'subscriptionmethod', 'configvalue' => '' ],
            [ 'configname' => 'subscriptionvalue', 'configvalue' => '' ],
            [ 'configname' => 'bankno', 'configvalue' => '' ],
            [ 'configname' => 'bankholder', 'configvalue' => '' ],
            [ 'configname' => 'bankcity', 'configvalue' => '' ],
            [ 'configname' => 'mybankaccount', 'configvalue' => '' ],
            [ 'configname' => 'mybankholder', 'configvalue' => '' ],
            [ 'configname' => 'myidentifier', 'configvalue' => '' ],
            [ 'configname' => 'myfixeddesc', 'configvalue' => '' ],
            [ 'configname' => 'debug', 'configvalue' => 'false' ],
            [ 'configname' => 'maxage', 'configvalue' => '60' ],
            [ 'configname' => 'autosubscriptionpayment', 'configvalue' => 'false' ],
            [ 'configname' => 'subscriptionlatestdownload', 'configvalue' => '' ],
            [ 'configname' => 'securehash', 'configvalue' => 'pleasechangethis' ],
            [ 'configname' => 'contractvalidation', 'configvalue' => '' ],
            [ 'configname' => 'version', 'configvalue' => '' ],
            [ 'configname' => 'original_invoice_value', 'configvalue' => '0' ],
            [ 'configname' => 'mybic', 'configvalue' => '' ],
            [ 'configname' => 'mycreditorid', 'configvalue' => '' ],
            [ 'configname' => 'bicno', 'configvalue' => '' ],
            [ 'configname' => 'enable_sepa', 'configvalue' => '1' ],
            [ 'configname' => 'customer_manref', 'configvalue' => '' ],
            [ 'configname' => 'customer_mandate', 'configvalue' => '' ]
        ]);

        return array(
            'status' => 'success',
            'description' => 'The incasso/direct debit module has been activated.'
        );

    } catch(Exception $e) {
        return array(
            'status' => 'error',
            'description' => 'The installation failed with ' . $e->getMessage()
        );
    }
}

/**
 * Deactivate the installation
 */
function incasso_deactivate() {
    try {
        Capsule::schema()->dropIfExists('mod_incasso');
        Capsule::schema()->dropIfExists('mod_incasso_batch');
        Capsule::schema()->dropIfExists('mod_incasso_mandates');

        return array(
            'status' => 'success',
            'description' => 'The incasso/direct debit module has been deactivated.'
        );
    } catch(Exception $e) {
        return array(
            'status' => 'error',
            'description' => 'Deactivating the incasso/direct debit module has failed with ' . $e->getMessage()
        );
    }
}

/**
 * Upgrade the module when a new version is released
 *
 * @param array $vars
 * @return void
 */
function incasso_upgrade($vars) {
    $version = $vars['version'];

    // Upgrading to version 3.23
    if ($version < 3.23) {
        Capsule::schema()->table('mod_incasso_batch',
            function($table) {
                $table->integer('batch_type')->after('invoice_id');
            }
        );
    }

    // Upgrading to version 3.24
    if ($version < 3.24) {
        Capsule::schema()->table('mod_incasso_batch',
            function($table) {
                $table->integer('date_frst');
                $table->integer('date_rcur');
            }
        );
    }

    // Upgrading to version 4.1
    if ($version < 4.1) {
        Capsule::table('mod_incasso')->insert([ 'configname' => 'payment_description_prefix', 'configvalue' => '' ]);
    }

    // Upgrading to version 4.2
    if ($version < 4.2) {
        Capsule::schema()->create(
            'mod_incasso_mandates',
            function($table) {
                $table->increments('uid');
                $table->string('timestamp');
                $table->string('ipaddress');
                $table->string('company_name');
                $table->string('company_address');
                $table->string('company_postcode');
                $table->string('company_city');
                $table->string('company_country');
                $table->string('company_creditor_id');
                $table->string('mandate_reference');
                $table->string('mandate_description');
                $table->integer('customer_id');
                $table->string('customer_name');
                $table->string('customer_address');
                $table->string('customer_postcode');
                $table->string('customer_city');
                $table->string('customer_country');
                $table->string('customer_bankaccount_number');
                $table->string('customer_bankaccount_bic');
                $table->string('customer_bankaccount_city');
                $table->text('customer_signature');
                $table->text('customer_signature_date');
            }
        );
    }
}

/**
 * Output data to screen
 *
 * @param array $vars
 * @return string
 */
function incasso_output($vars) {
    $smarty = new Smarty;
    $incasso = new Incasso;

    // initialize the template class
    $smarty->caching = false;
    $smarty->compile_dir = $GLOBALS['templates_compiledir'];
    $smarty->template_dir = dirname(__FILE__) . "/templates";

    // Get all settings
    $settings = Capsule::table('mod_incasso')->select('configname', 'configvalue')->get();
    foreach($settings as $key => $setting) {
        eval("\$" . $setting->configname . " = \$setting->configvalue;");
    }

    // Get all settings
    $settings = Setting::getSettings();

    // Choose which page to render
    if (array_key_exists('page', $_GET)) {

        $pages = new Incasso\Core\AdminPages();

        if ($_POST) {
            echo $pages->post($_GET['page'], $smarty, $vars);
        } else {
            echo $pages->get($_GET['page'], $smarty, $vars);
        }
        return true;
    }


    // Check the enabled payment gateways
    $accepted_gateways[] = 'directdebit';
    $accepted_gateways[] = 'sepaincasso';

    $activated_gateways = Capsule::table('tblpaymentgateways')->distinct('gateway')->select('gateway');
    foreach ($activated_gateways->get() as $gateway) {
        if (in_array($gateway->gateway, $accepted_gateways)) {
            $gateways[] = $gateway->gateway;
        }
    }

    // Set a primary and legacy gateway that we can use later to decide where to store
    // and read transactions from
    if (in_array('sepaincasso', $gateways)) {
        $gateway = 'sepaincasso';
        $legacy_gateway = 'directdebit';
    } else {
        $gateway = 'directdebit';
        $legacy_gateway = '';
    }

    // Check if this was an AJAX request, if not, display our usual template
    if(array_key_exists('ajax', $_GET)) {
        // We're defining WHMCS as that is the internal security system
        // from WHMCS itself, we're still adding additional security to that though
        // as simply defining WHMCS to true can be dangerous
        if(array_key_exists('hash', $_GET)) {
            if($_GET['hash'] != $securehash) {
                die("Unauthorized access. No direct access allowed to this module.");
            } else {
                define(WHMCS, true);
            }
        } else {
            define(WHMCS, true);
        }

        // Only display what is needed
        $smarty->assign('ajax', true);
        $smarty->assign('modulename', $modulename);

        // Check for the subscription field / method of payment
        if($_GET['subscriptionchange']) {
            $smarty->assign('subscriptionchange', true);

            // select all values from $_GET['subscriptionmethod'];
            $subscriptionmethod = $_GET['subscriptionmethod'];
            $subscription_value_dropdown = Capsule::table('tblcustomfields')->
                                            select('fieldoptions')->
                                            where('type', '=', 'client')->
                                            where('id', '=', $subscriptionmethod)->get()[0]->fieldoptions;

            if(!preg_match('/\,/i', $subscription_value_dropdown)) {
                if($subscriptionmethod == 9998 or $subscriptionmethod == 9999) {
                    $smarty->assign('subscription_payment_selector_default', true);
                } else {
                    $smarty->assign('subscription_payment_selector_error', true);
                }
            }

            $subscription_value_dropdown = explode(',', $subscription_value_dropdown);
            foreach($subscription_value_dropdown as $option) {
                $dropdown[$option] = $option;
            }

            $smarty->assign('subscription_value', $dropdown);
        }

        // Display the list of invoices in this batch
        if($_GET['batchchange']) {
            $smarty->assign('batchchange', true);

            // get all batch data
            $batch = $_GET['batch'];
            $smarty->assign('batch', $_GET['batch']);

            // Get all batch details
            $invoices = Capsule::table('mod_incasso_batch')
                        ->join('tblinvoices', 'tblinvoices.id', '=', 'mod_incasso_batch.invoice_id')
                        ->join('tblclients', 'tblinvoices.userid', '=', 'tblclients.id')
                        ->select(
                            'mod_incasso_batch.uid',
                            'mod_incasso_batch.invoice_id',
                            'mod_incasso_batch.client_id',
                            'mod_incasso_batch.invoice_total',
                            'mod_incasso_batch.batch_type',
                            'mod_incasso_batch.date_frst',
                            'mod_incasso_batch.date_rcur',
                            'tblinvoices.id',
                            'tblinvoices.invoicenum',
                            'tblinvoices.total',
                            'tblinvoices.userid',
                            'tblinvoices.date',
                            'tblinvoices.duedate',
                            'tblinvoices.status',
                            'tblclients.city',
                            'tblclients.firstname',
                            'tblclients.lastname',
                            'tblclients.companyname'
                        )
                        ->where('mod_incasso_batch.batch_id', '=', $batch)
                        ->orderBy('tblinvoices.id', 'asc');

            foreach($invoices->get() as $invoice) {
                // This needs to be backwards compatible when you're not using original invoice value
                if($original_invoice_value == 1) {
                    if($invoice->invoice_total == 0) {
                        $invoice->invoice_total = $invoice->total;
                    }
                } else {
                    $invoice->invoice_total = $invoice->total;
                }

                // Get current mandate reference if it was set to the default
                // This makes sure things are backwards compatible-ish
                if ($invoice->batch_type == 0) {
                    $clientsdetails = localAPI('GetClientsDetails', array('clientid' => $invoice->userid, 'stats' => true), '');
                    $customfields = Helper::customFieldsToArray($clientsdetails['client']['customfields']);
                    $mandate_reference = $customfields[$settings->customer_manref];

                    if (Helper::frstOrRcur($invoice->userid, $mandate_reference, $gateways) == 'RCUR') {
                        $batch_type = 2;
                    } else {
                        $batch_type = 1;
                    }
                }

                // Populate our data
                $batch_data[$invoice->uid] = (array) $invoice;
                $batch_data[$invoice->uid]['batchType'] = $batch_type;

                // Count totals
                $invoice_total = $invoice_total + $invoice->total;
                $batch_invoice_total = $batch_invoice_total + $invoice->invoice_total;

                // Set some dates
                $date_frst = $invoice->date_frst;
                $date_rcur = $invoice->date_rcur;
            }

            // Get a count of invoices
            $invoices_count = $invoices->count();
            $invoices_unpaid = $invoices->where('tblinvoices.status', '=', 'Unpaid')->count();

            // Populate our smarty variables
            $smarty->assign('batchdata', $batch_data);
            $smarty->assign('invoicetotal', number_format($invoice_total, 2));
            $smarty->assign('batchinvtotal', number_format($batch_invoice_total, 2));
            $smarty->assign('invoicenumber', $invoices_count);
            $smarty->assign('invoicenumberunpaid', $invoices_unpaid);
            $smarty->assign('securehash', $securehash);
            $smarty->assign('original_invoice_value', $original_invoice_value);
            $smarty->assign('incasso_batch_location', "../modules/addons/incasso/sepa.php"); // only support SEPA
            $smarty->assign('incasso_unique_id', time());
            $smarty->assign('date_frst', $date_frst);
            $smarty->assign('date_rcur', $date_rcur);

            // This is required when the setting original invoice value is used
            if($original_invoice_value == 1) {
                $smarty->assign('batchtotal', number_format($batch_invoice_total, 2));
            } else {
                $smarty->assign('batchtotal', number_format($invoice_total, 2));
            }
        }

        // Return output and stop further processing of any other templates
        $smarty->display(dirname(__FILE__) . '/templates/admin/mod_incasso_overview.tpl');
        die();
    }
    else {
        if($_POST) {
            $smarty->assign('formsubmit', true);
        }

        if($_POST['submitform'] == TRUE)
        {
            // Create variables for our current settings
            foreach($_POST as $key => $value) {
                eval("\$" . $key . " = \$value;");
            }

            // Run some tests to verify our information
            if(!IsoCodes\Iban::validate($mybankaccount)) {
                $error = true;
                $errors[] = "You have enabled SEPA but your bank account is not a valid IBAN number.";
            }
            if(!IsoCodes\SwiftBic::validate($mybic)) {
                $error = true;
                $errors[] = "You have enabled SEPA but your BIC number is invalid.";
            }

            // Display errors
            if($error) {
                // If errors are true, change the information box
                $smarty->assign('infosubject', 'Errors found:');
                $smarty->assign('error', true);
                $smarty->assign('errors', $errors);
            }
            else {
                // Update all of our POST fields into the database
                $excludePostFields[] = 'submitform';
                $excludePostFields[] = 'token';
                $excludePostFields[] = 'apipassword1';
                $excludePostFields[] = 'apipassword2';
                $excludePostFields[] = 'tab';

                // Update all of our POST fields into the database
                foreach($_POST as $key => $value) {
                    if(!in_array($key, $excludePostFields)) {
                        $config_count = Capsule::table('mod_incasso')->where('configname', '=', $key)->count();
                        if($config_count == 1) {
                            Capsule::table('mod_incasso')->where('configname', '=', $key)->update(['configvalue' => $value]);
                        } else {
                            Capsule::table('mod_incasso')->insert([ 'configname' => $key, 'configvalue' => $value ]);
                        }
                    }
                }

                // set the content for the information box
                $smarty->assign('infosubject', 'Changes Saved Successfully!');
                $smarty->assign('infostring', 'Configuration settings have been saved succesfully');

                if($selectedtab == false) {
                    $selectedtab = "tab4";
                }
                $smarty->assign('selectedtab', $selectedtab);
            }

        } // end submit form

        // Create a new direct debit batch
        if($_POST['createbatch']) {
            $chkchecked = 0;

            // Find last batch id
            $lastBatchId = Capsule::table('mod_incasso_batch')->distinct('batch_id')->orderBy('batch_id', 'desc');

            if($lastBatchId->count() == 0) {
                $batch_id = 1;
            } else {
                $batch_id = $lastBatchId->get()[0]->batch_id + 1;
            }

            // Create a new batch
            foreach($_POST as $key => $value) {
                if(preg_match('/chk_create/i', $key)) {
                    if($value == "on") {
                        $invoice    = explode('_', $key);
                        $invoice_id = $invoice[2];
                        $client_id  = $invoice[3];

                        // Set the batch type
                        if ($_POST["select_batchType_{$invoice_id}_{$client_id}"] == 0) {
                            // Get current mandate reference
                            $clientsdetails = localAPI('GetClientsDetails', array('clientid' => $client_id, 'stats' => true), '');
                            $customfields = Helper::customFieldsToArray($clientsdetails['client']['customfields']);
                            $mandate_reference = $customfields[$settings->customer_manref];

                            if (Helper::frstOrRcur($client_id, $mandate_reference, $gateways) == 'RCUR') {
                                $batch_type = 2;
                            } else {
                                $batch_type = 1;
                            }
                        } else {
                            $batch_type = $_POST["select_batchType_{$invoice_id}_{$client_id}"];
                        }

                        // Create an entry in the database
                        Capsule::table('mod_incasso_batch')->insert([
                            'batch_id'      => $batch_id,
                            'invoice_id'    => $invoice_id,
                            'client_id'     => $client_id,
                            'date'          => time(),
                            'date_frst'     => strtotime(str_replace("/", "-", $_POST['date-frst'])),
                            'date_rcur'     => strtotime(str_replace("/", "-", $_POST['date-rcur'])),
                            'batch_type'    => $batch_type,
                            'invoice_total' => $invoice[4] / 100
                        ]);

                        $chkchecked++;
                    }
                }
            }

            // If ok, redirect to view subscription batch
            // enable tab and set dropdown
            if($chkchecked > 0) {
                $selectedtab = "tab3";
            }

            $smarty->assign('formsubmit', true);
            $smarty->assign('infosubject', 'Batch #' . $batch_id . ' created.');
            $smarty->assign('infostring', 'A new batch has been created succesfully. Please choose the batch from the form below.');

        }

        // Remove invoices from an existing batch
        if($_POST['removefrombatch']) {
            $removed = 0;

            // Remove all invoices
            foreach($_POST as $key => $value) {
                if(preg_match('/chk_manage_/i', $key)) {
                    if($value == 'on') {
                        Capsule::table('mod_incasso_batch')->where('invoice_id', '=', explode('_', $key)[2])->delete();
                        $removed++;
                    }
                }
            }

            if($removed == 0) {
                $smarty->assign('infosubject', 'No items have been deleted.');
                $smarty->assign('infostring', 'Please make sure you have selected the invoices you would like to remove.');
                $selectedtab = "tab3";
            } else {
                $smarty->assign('infosubject', $removed . ' item(s) have been deleted.');
                $smarty->assign('infostring', 'If you want to add them to a new batch, please click the \'Create Subscription Batch\' tab.');
            }

            if($selectedtab == false) {
                $selectedtab = "tab2";
            }

            $smarty->assign('selectedtab', $selectedtab);
        }

        // Process payments
        if($_POST['processpayment']) {
            $invoices_paid = 0;

            // Make sure we are not hit by any timeouts
            ini_set("mysql.connect_timeout", "1200");
            set_time_limit(0);

            // Get the current admin user, we'll use that to process the payment
            if(is_numeric($_SESSION['adminid'])) {
                $admin_username = Capsule::table('tbladmins')->select('username')->where('id', '=', $_SESSION['adminid'])->get()[0]->username;
            } else {
                $admin_username = 'unknown';
            }

            foreach($_POST as $key => $value) {
                if(preg_match('/chk_manage_/i', $key)) {
                    if($value == 'on') {
                        $invoice            = explode('_', $key);
                        $invoice_id         = $invoice[2];
                        $client_id          = $invoice[3];
                        $current_time       = time();
                        $clientsdetails     = localAPI('GetClientsDetails', array('clientid' => $client_id, 'stats' => true), '');
                        $customfields       = Helper::customFieldsToArray($clientsdetails['client']['customfields']);
                        $mandate_reference  = $customfields[$settings->customer_manref];
                        $trans_id           = "T{$current_time}C{$client_id}I{$invoice_id}M{$mandate_reference}";
                        $amount             = $invoice[4] / 100;

                        // Create API call and use the primary gateway for it
                        $values         = ['invoiceid' => $invoice_id,
                                            'transid'   => $trans_id,
                                            'amount'    => $amount,
                                            'gateway'   => $gateway];
                        $result         = localAPI('addinvoicepayment', $values, $admin_username);
                        $invoices_paid++;
                    }
                }
            }

            if($invoices_paid == 0) {
                $smarty->assign('infosubject', 'No items have been set as paid.');
                $smarty->assign('infostring', 'Please make sure you have selected any invoices.');
            } else {
                $smarty->assign('infosubject', $invoices_paid . ' item(s) have been set as paid.');
                $smarty->assign('infostring', '');
            }

            if($selectedtab == false) {
                $selectedtab = "tab1";
            }
            $smarty->assign('selectedtab', $selectedtab);
        }

        /*
         *
         * This is where we load our page settings, our configurations and invoice
         * lists. We populate our data here that will be used by the templating
         * engine.
         *
         */

        // Get all settings
        $settings = Capsule::table('mod_incasso')->select('configname', 'configvalue')->get();

        if(!$_POST['submitform']) {
            // Set current settings
            foreach($settings as $key => $setting) {
                eval("\$" . $setting->configname . " = \$setting->configvalue;");
            }
        } else {
            // Set new and old settings
            foreach($_POST as $key => $setting) {
                eval("\$" . $key . " = \$setting;");
            }
            foreach($settings as $key => $setting) {
                eval("\$" . $setting->configname . "2 = \$setting->configvalue;");
            }
        }


        $settings = Setting::getSettings();

        // Get all invoices
        $invoices = Capsule::table('tblinvoices')
                        ->join('tblclients', 'tblclients.id', '=', 'tblinvoices.userid')
                        ->leftJoin('mod_incasso_batch', 'mod_incasso_batch.invoice_id', '=', 'tblinvoices.id')
                        ->select(
                            'tblinvoices.id',
                            'tblinvoices.invoicenum',
                            'tblinvoices.total',
                            'tblinvoices.userid',
                            'tblinvoices.date',
                            'tblinvoices.duedate',
                            'tblinvoices.paymentmethod',
                            'tblclients.firstname',
                            'tblclients.lastname',
                            'tblclients.companyname',
                            'tblclients.city',
                            'tblclients.defaultgateway',
                            'mod_incasso_batch.invoice_id as batch_invoiceid'
                        )
                        ->where('tblinvoices.status', '=', 'Unpaid')
			->where('tblinvoices.total', '!=', '0.00')
                        ->orderBy('tblinvoices.id', 'asc');

        // Select invoices
        if($subscriptionmethod == 9999) {
            // Only select invoices that of which the customer uses the directdebit gateway by default
            $invoices->where(function ($query) use ($gateway, $legacy_gateway) {
                $query->where('tblclients.defaultgateway', '=', $gateway);

                if ($legacy_gateway != '') {
                    $query->orWhere('tblclients.defaultgateway', '=', $legacy_gateway);
                }
            });
        } elseif($subscriptionmethod == 9998) {
            // Only select invoices that were use the directdebit gateway
            $invoices->where(function ($query) use ($gateway, $legacy_gateway) {
                $query->where('tblinvoices.paymentmethod', '=', $gateway);

                if ($legacy_gateway != '') {
                    $query->orWhere('tblinvoices.paymentmethod', '=', $legacy_gateway);
                }
            });
        } else {
            // Default functionality since version 2
            $invoices->join('tblcustomfieldsvalues', 'tblcustomfieldsvalues.relid', '=', 'tblinvoices.userid');
            $invoices->where('tblcustomfieldsvalues.fieldid', '=', $subscriptionmethod)->where('tblcustomfieldsvalues.value', '=', $subscriptionvalue);
        }

        // Loop through all invoices and create an array with customer details
        $client_cache = array();
        $customer_errors = array();
        $customers_with_errors = array();

        foreach($invoices->get() as $invoice) {
            if($invoice->batch_invoiceid == 0) {

                // This call used to be slow for two reasons:
                // * stats => true gathers a lot of information
                // * it ran several times for every customer
                if (!array_key_exists($invoice->userid, $client_cache)) {
                    $client_cache[$invoice->userid] = localAPI('GetClientsDetails', array('clientid' => $invoice->userid, 'stats' => false), '');
                }

                // Retrieve client details from cache
                $clientsdetails = $client_cache[$invoice->userid];

                $customfields = Helper::customFieldsToArray($clientsdetails['client']['customfields']);
                $mandate_reference = $customfields[$settings->customer_manref];

                if (Helper::frstOrRcur($invoice->userid, $mandate_reference, $gateways) == 'RCUR') {
                    $batch_type = 2;
                } else {
                    $batch_type = 1;
                }

                // Only include an invoice if the total value isn't below zero
                // We multiply by 100 because we are storing the variable as a string/float and this converts it to an integer
                // which allows us to validate it with filter_var()
                if (filter_var($invoice->total * 100, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) !== false) {
                    $batch_invoices[$invoice->id] = (array) $invoice;
                    $batch_invoices[$invoice->id]['batchType'] = $batch_type;
                    $totalamount = $totalamount + $invoice->total;

                    // Run additional tests
                    $mandate = new Mandate();
                    $validator = new MandateValidator();

                    $mandate->customer_name = $customfields[$settings->bankholder];
                    $mandate->customer_bankaccount_city = $customfields[$settings->bankcity];
                    $mandate->customer_bankaccount_number = $customfields[$settings->bankno];
                    $mandate->customer_bankaccount_bic = $customfields[$settings->bicno];

                    $validation_errors = $mandate->validates();
                    $valid_signature = $validator->validateSignatureDate($customfields[$settings->customer_mandate]);

                    if (is_array($validation_errors) OR $valid_signature == false) {
                        // Get the customer name
                        if (strlen($invoice->companyname) != 0) {
                            $customer_name = "$invoice->companyname - $invoice->firstname $invoice->lastname";
                        } else {
                            $customer_name = "$invoice->firstname $invoice->lastname";
                        }

                        // Add users with errors to an array that we can use
                        // in the template to mark them red.
                        $customers_with_errors[] = $invoice->userid;

                        if (is_array($validation_errors)) {
                            foreach ($validation_errors as $type => $error) {
                                if ($type == 'customer_bankaccount_number') {
                                    $customer_errors[] = "* <a href='clientsprofile.php?userid=$invoice->userid' style='text-decoration: underline;'>$customer_name</a> has an invalid bank account number";
                                }

                                if ($type == 'customer_bankaccount_city') {
                                    $customer_errors[] = "* <a href='clientsprofile.php?userid=$invoice->userid' style='text-decoration: underline;'>$customer_name</a> has an invalid bank account city";
                                }

                                if ($type == 'customer_bankaccount_bic') {
                                    $customer_errors[] = "* <a href='clientsprofile.php?userid=$invoice->userid' style='text-decoration: underline;'>$customer_name</a> has an invalid bank account bic";
                                }
                            }
                        }

                        if ($valid_signature == false) {
                            $customer_errors[] = "* <a href='clientsprofile.php?userid=$invoice->userid' style='text-decoration: underline;'>$customer_name</a> has an invalid signature date.";
                        }
                    }
                }
            }
        }

        if (!empty($customers_with_errors)) {
            $smarty->assign('customer_error', true);
            $smarty->assign('customer_errors', array_unique($customer_errors));
            $smarty->assign('$customers_with_errors', $customers_with_errors);
            $smarty->assign('customer_error_subject', 'One or more customers contain invalid direct debit data');
        }

        $smarty->assign('batch_invoices', $batch_invoices);

        // Get a list of all batches
        $all_batches = Capsule::table('mod_incasso_batch')->distinct('batch_id')->select('batch_id', 'date')->orderBy('batch_id', 'desc')->get();
        foreach($all_batches as $batch) {
            $batches[$batch->batch_id]['date'] = date('d-m-Y', $batch->date);
        }
        $smarty->assign('batches', $batches);

        // set other variables
        $smarty->assign('totalamount', number_format($totalamount, 2, ',', '.'));
        $smarty->assign('totalinvoices', count($batch_invoices));
        $smarty->assign('mybankaccount', $mybankaccount);
        $smarty->assign('mybankholder', $mybankholder);
        $smarty->assign('myidentifier', $myidentifier);
        $smarty->assign('myfixeddesc', $myfixeddesc);
        $smarty->assign('mybic', $mybic);
        $smarty->assign('mycreditorid', $mycreditorid);
        $smarty->assign('securehash', $securehash);

        // Set variables
        $custom_fields = Capsule::table('tblcustomfields')->select('id', 'fieldname', 'fieldoptions')->where('type', '=', 'client');
        if($custom_fields->count() == 0) {
            $smarty->assign('nofieldsfound', true);
        } else {

            foreach($custom_fields->get() as $field) {
                $subscription_options[$field->id] = $field->fieldname;
                $subscription_fieldoptions[$field->id] = $field->fieldoptions;
            }

            // Only select invoices that have been marked
            $subscription_payment_selector = $subscription_options;
            $subscription_payment_selector[9998] = '-- Payment method: direct debit (invoices)';
            $subscription_payment_selector[9999] = '-- Payment method: direct debit (customer)';

            // Make sure something is selected, eg. after the installation
            if($subscriptionmethod == 0) {
                $subscriptionmethod = 9999;
            }

            // Select the options for $subscriptionmethod
            $subscription_value_dropdown = $subscription_fieldoptions[$subscriptionmethod];

            if(!preg_match('/\,/i', $subscription_value_dropdown)) {
                if($subscriptionmethod == 9998 or $subscriptionmethod == 9999) {
                    $smarty->assign('subscription_payment_selector_default', true);
                } else {
                    $smarty->assign('subscription_payment_selector_error', true);
                }
            }

            // Select a city
            $subscription_city_options = $subscription_options;
            $subscription_city_options[9999] = "Use client city (non-custom)";

            // Select a mandate reference
            $subscription_options_manref = $subscription_options;
            $subscription_options_manref[9999] = "Use default format of CID-{USERID}";

            // Select a contract validation option (currently not implemented)
            $contract_options[9999] = "No contract validation";
            foreach($subscription_options as $key => $val) {
                $contract_options[$key] = "Validate contracts with --- {$val}";
            }

            // Use the original invoice value
            $payment_options[0] = "No";
            $payment_options[1] = "Yes";

            // Enable SEPA (this used to have more options, but we only support SEPA now)
            $sepa_options[1] = "Yes";
            $sepa_options[2] = "No, download as CSV";

            // Select the subscription payment method custom field
            $subscription_value_dropdown = explode(",", $subscription_value_dropdown);
            foreach($subscription_value_dropdown as $key => $value) {
                $subscription_values[$value] = $value;
            }

            // Create a list of selectable countries and set the default value
            if(strlen($mycountry) == 0) {
                $country_list_default = 'NL';
            } else {
                $country_list_default = $mycountry;
            }

            // Get the current WHMCS version and set the right variables
            $whmcsVersion = $GLOBALS['CONFIG']['Version'][0];
            $smarty->assign('country_list', $incasso->generateCountryList($whmcsVersion));
            $smarty->assign('country_list_default', $country_list_default);

            // API functionality
            $smarty->assign('api_user', $api_user);
            $smarty->assign('api_users', Helper::getAdmins());

            // Set all SMARTY variables
            $smarty->assign('contract_options', $contract_options);
            $smarty->assign('payment_options', $payment_options);
            $smarty->assign('subscription_options', $subscription_options);
            $smarty->assign('subscription_city_options', $subscription_city_options);
            $smarty->assign('subscription_options_manref', $subscription_options_manref);
            $smarty->assign('subscription_value', $subscription_values);
            $smarty->assign('subscription_field_default', $subscriptionmethod);
            $smarty->assign('subscription_value_default', $subscriptionvalue);
            $smarty->assign('subscription_number_default', $bankno);
            $smarty->assign('subscription_holder_default', $bankholder);
            $smarty->assign('subscription_city_default', $bankcity);
            $smarty->assign('subscription_bic_default', $bicno);
            $smarty->assign('subscription_manref_default', $customer_manref);
            $smarty->assign('subscription_mandate_default', $customer_mandate);
            $smarty->assign('payment_sepa_default', $enable_sepa);
            $smarty->assign('sepa_options', $sepa_options);
            $smarty->assign('contract_option_default', $contractvalidation);
            $smarty->assign('payment_option_default', $original_invoice_value);
            $smarty->assign('current_version', $vars['version']);
            $smarty->assign('payment_description_prefix', $payment_description_prefix);
            $smarty->assign('subscription_payment_selector', $subscription_payment_selector);
            $smarty->assign('myaddress', $myaddress);
            $smarty->assign('mypostcode', $mypostcode);
            $smarty->assign('mycity', $mycity);
        }

        if($selectedtab == false) {
            $selectedtab = "tab1";
        }
        $smarty->assign('selectedtab', $selectedtab);
    }

    // Display the end result on screen
    $smarty->display(dirname(__FILE__) . '/templates/admin/mod_incasso_overview.tpl');
}

function incasso_sidebar($vars) {
    $smarty = new Smarty;
    $incasso = new Incasso;

    $pages = new Incasso\Core\AdminPages();

    // initialize the template class
    $smarty->caching = false;
    $smarty->compile_dir = $GLOBALS['templates_compiledir'];
    $smarty->template_dir = dirname(__FILE__) . "/templates";

    // Get a list of all pages
    $smarty->assign('language', $vars['_lang']);
    $smarty->assign('pages', $pages->getPages());
    $smarty->assign('modulelink', $vars['modulelink']);

    return $smarty->fetch('admin/sidebar.tpl');
}

function incasso_clientarea($vars) {
    $pages = new Incasso\Core\ClientPages();

    if ($_POST) {
        return $pages->post($_GET['page'], null, $vars);
    } else {
        return $pages->get($_GET['page'], null, $vars);
    }
}

?>

<?php
/**
 * Incasso direct debit module
 *
 * SEPA download file.
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

// Set up
ini_set('memory_limit','64M');
ini_set("mysql.connect_timeout", "1200");
set_time_limit(0);
error_reporting(0);

use Illuminate\Database\Capsule\Manager as Capsule;

// Include our classes
include("../../../configuration.php");
include("../../../init.php");
include("class/sepa.php");
include("class/isocodes/IsoCodeInterface.php");
include("class/isocodes/Iban.php");
include("class/isocodes/SwiftBic.php");

// Get all settings
$settings = Capsule::table('mod_incasso')->select('configname', 'configvalue')->get();
foreach($settings as $key => $setting) {
    eval("\$" . $setting->configname . " = \$setting->configvalue;");
}

// Security check
if($_GET['hash'] != $securehash) {
    die("Unauthorized access. No direct access allowed.");
}

// Check for proforma 
$proforma = Capsule::table('tblconfiguration')->select('value')->where('setting', '=', 'SequentialInvoiceNumbering')->get()[0]->value;
$normalize_characters = [
    'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Å'=>'A', 'Ä'=>'A', 'Æ'=>'AE', 'Ç'=>'C',
    'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ð'=>'Eth',
    'Ñ'=>'N', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
    'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
    'á'=>'a', 'à'=>'a', 'â'=>'a', 'ã'=>'a', 'å'=>'a', 'ä'=>'a', 'æ'=>'ae', 'ç'=>'c',
    'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'eth', 
    'ē' => 'e', 'ñ'=>'n', 'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',
    'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y',
    'ß'=>'ss', 'þ'=>'thorn', 'ÿ'=>'y',
    '&'=>' ', '@'=>'at', '#'=>'h', '$'=>'s', '%'=>'perc', '^'=>'-','*'=>'-'
];

// A simple function to replace all characters
function normalize_str($string, $character_set) {
    if(is_array($character_set)) {
        foreach($character_set as $original_chr => $new_chr) {
            $string = str_replace($original_chr, $new_chr, $string);
        }
        return $string;
    } else {
        return false;
    }
}

try {
    // Setup our SEPA class
    $sepa = new clsGenerateSepaXML();

    if($_GET['batch']) {
        $batch_id = $_GET['batch'];
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

        // Add a where clause for unpaid invoices
        if($_GET['unpaid']) {
            $invoices->where('tblinvoices.status', '=', 'Unpaid');
        }

        if($invoices->count() > 0) {
            // Loop through all invoices and build a SEPA XML file
            foreach($invoices->get() as $invoice) {
                // Set some variables
                $total = $invoice->total * 100;
                $invoice_total = $invoice->invoice_total * 100;
                $userid = $invoice->userid;

                // Backwards compatible with old batches
                if($original_invoice_value == 1) {
                    if($invoice_total == 0) {
                        $invoice_total = $total;
                    }
                } else {
                    $invoice_total = $total;
                }

                // Set invoicenum and check if it's not empty
                $invoice_number = $invoice->invoicenum;
                if(strlen($invoice_number) == 0) {
                    $invoice_number = $invoice->id;
                }

                // Fill up our customer details
                $customer_options = Capsule::table('tblcustomfieldsvalues')->where('relid', '=', $userid)->select('fieldid', 'value')->get();
                foreach($customer_options as $option) {
                    if($option->fieldid == $bankno) {
                        $clientbank = $option->value;
                    }
                    if($option->fieldid == $bankholder) {
                        $clientholder = normalize_str($option->value, $normalize_characters);
                    }
                    if($option->fieldid == $bankcity && $bankcity != 9999) {
                        $clientcity = normalize_str($option->value, $normalize_characters);
                    }
                    if($option->fieldid == $customer_manref && $customer_manref != 9999) {
                        $customermanref = $option->value;
                    }
                    if($option->fieldid == $customer_mandate) {
                        $customermandate = $option->value;
                    }
                    if($option->fieldid == $bicno) {
                        $clientbic = $option->value;
                    }
                }

                // Set a default reference when required
                if($customer_manref == 9999 OR strlen($customermanref) == 0) {
                    $customermanref = "CID-{$userid}";
                }

                // Set the right city
                if($bankcity == 9999) {
                    $clientcity = $invoice->city;
                }

                // Set the paydate + 1
                $paydate = date('Y-m-d', strtotime('+1 days'));

                // Verify your own settings
                if(!IsoCodes\Iban::validate($mybankaccount)) {
                    $error = true;
                    $errors[] = "You have enabled SEPA but your bank account ({$mybankaccount}}) is not a valid IBAN number.";
                }
                if(!IsoCodes\SwiftBic::validate($mybic)) {
                    $error = true;
                    $errors[] = "You have enabled SEPA but your BIC number ({$mybic}) is invalid.";
                }

                // Customer checks
                if(strlen($clientholder) == 0) {
                    $error = true;
                    $errors[] = "Bank account holder is incorrect. It can't be empty (userid: {$userid}).";
                }
                if(!IsoCodes\Iban::validate($clientbank)) {
                    $error = true;
                    $errors[] = "You have enabled SEPA but {$clientholder}'s (userid: {$userid}) bank account ({$clientbank}) is not a valid IBAN number.";
                }
                if(!IsoCodes\SwiftBic::validate($clientbic)) {
                    $error = true;
                    $errors[] = "You have enabled SEPA but {$clientholder}'s (userid: {$userid}) BIC number ({$clientbic}) is invalid.";
                }
                if(!preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $customermandate)) {
                    $error = true;
                    $errors[] = "You have enabled SEPA but the mandate's signing date ({$customermandate}) is incorrect for {$clientholder} (userid: {$userid}), it doesn't match YYYY-MM-DD.";
                }

                // Display errors
                if($error == true) {
                    echo "<p>Some errors occurred:</p>\n<ul>\n";
                    foreach($errors as $errorid => $error) {
                        echo "<li>{$error}</li>\n";
                    }
                    echo "</ul>\n";
                    die();
                }

                // Set up FRST or RCUR.
                $previous_payments  = Capsule::table('tblaccounts')->where('gateway', '=', 'directdebit')->where('userid', '=', $invoice->userid)->count();
                if($_GET['batchType'] != 1 AND $_GET['batchType'] != 2) {
                    if($invoice->batch_type == 0) {
                        // This is our default setting: automatic
                        if($previous_payments >= 1) {
                            $batch_type = "RCUR";
                        } else {
                            $batch_type = "FRST";
                        }
                    } else {
                        // Override: per invoice specified when creating a batch
                        if($invoice->batch_type == 1) {
                            $batch_type = "FRST";
                        }
                        if($invoice->batch_type == 2) {
                            $batch_type = "RCUR";
                        }
                    }
                } else {
                    // Override: when downloading a batch
                    if($_GET['batchType'] == 1) {
                        $batch_type = "FRST";
                    }
                    if($_GET['batchType'] == 2) {
                        $batch_type = "RCUR";
                    }
                }

                // Set up the paydate
                if($batch_type == "FRST") {
                    $paydate = date("Y-m-d", $invoice->date_frst);
                }
                if($batch_type == "RCUR") {
                    $paydate = date("Y-m-d", $invoice->date_rcur);
                }

                // Make sure $paydate always works
                if($paydate == '1970-01-01') {
                    $paydate = date('Y-m-d', strtotime('+3 days'));
                }

                // Construct info field
                $infounstruct = trim(Lang::trans('invoicenumber')) . ': ' . $invoice_number;
                if(strlen($payment_description_prefix) != 0) {
                    $infounstruct = "{$payment_description_prefix} - $infounstruct";
                }

                // Set up the SEPA XML file
                $payment = [
                    // Creditor details
                    'PayAmount' => $invoice_total / 100,    // The total amount for this invoice in decimals
                    'IbanCr' => $mybankaccount,             // Your own IBAN bank account (the creditor)
                    'BicCr' => $mybic,                      // Your own BIC number (the creditor)
                    'CrName' => $mybankholder,              // The name of the account holder (the creditor)
                    'CreditorName' => $mybankholder,        // The name of the account holder (the creditor)
                    'CreditorID' => $mycreditorid,          // The creditor ID to identify the creditor. This ID is supplied by your bank

                    // Debitor details
                    'IbanDb' => $clientbank,                // The IBAN bank account of the customer
                    'BicDb' => $clientbic,                  // The BIC number of the customer
                    'UniqueID' => $batch_id,                // This is a unique identifier for this payment. We use the batch ID for this
                    'PayDate' => $paydate,                  // The date the invoice has to be paid
                    'UniqueIdentifier' => $invoice->uid,    // This identifier is used to identify the transaction
                    'InfoUnstruct' => $infounstruct,        // Information detailing the transaction such as an invoice number
                    'DbtrName' => $clientholder,            // The name of the customer
                    'MandateID' => $customermanref,         // The unique mandate identifiaction
                    'MandateDateSig' => $customermandate,   // The date the mandate was signed by the customer
                ];

                // Add the payment to the batch
                if($enable_sepa == 1) {
                    $sepa->addPayment($payment, $batch_type);
                }
                if($enable_sepa == 2) {
                    $payment['SeqTp'] = $batch_type;
                    $sepa_csv[] = $payment;
                }

                unset($customermanref);
                unset($customermandate);
            }

            // Download as SEPA XML
            if($enable_sepa == 1) {
                // This payment request needs to be initiated
                $initiated_by = [
                    'Nm' => $mybankholder,      // Identification of the initiating company
                    'PstlAdr' => '',            // Postal address (optional)
                    'Id' => '',                 // Identification (optional)
                    'CtryOfRes' => '',          // Country of residence (optional)
                    'CtctDtls' => '',           // Contact details (optional)
                ];

                // Set the message ID and create an XML file
                $message_id = "{$myidentifier}-{$batch_id}-" . time();
                $output = $sepa->generateXML($initiated_by, $message_id);

                // Set our headers
                header("Content-Type: text/xml");
                header("Content-Disposition: attachment; filename=\"{$myidentifier}-{$batch_id}.xml\"");
                header("Cache-Control: no-cache, must-revalidate");
                header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
                echo $output;
            }

            // Download as CSV
            if($enable_sepa == 2) {
                // Set our headers
                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=\"{$myidentifier}-{$batch_id}.csv\"");
                header("Cache-Control: no-cache, must-revalidate");
                header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

                // Direct output instead of saving it to a file
                $csv_output = fopen('php://output', 'w');

                // Loop through all entries and add them to the CSV file
                foreach($sepa_csv as $sepa_csv_line) {
                    fputcsv($csv_output, $sepa_csv_line);
                }

                // Close the pointer to php://output
                fclose($csv_output);
            }
        }
    } else {
        // If they managed to get passed the security checks, throw up another simple barrier just to make one bit more difficult
        die("Unauthorized access. No direct access allowed.");
    }

} catch(Exception $e) {
    echo $e->getMessage();
}
?>

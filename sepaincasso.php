<?php
/**
 * Incasso direct debit payment gateway
 *
 * Support is available via info@sensson.net.
 *
 * @author      Sensson <info@sensson.net>
 * @copyright   2004-2017 Sensson
 * @license     This software is furnished under a license and may be used and copied
 *              only  in  accordance  with  the  terms  of such  license and with the
 *              inclusion of the above copyright notice.  This software  or any other
 *              copies thereof may not be provided or otherwise made available to any
 *              other person.  No title to and  ownership of the  software is  hereby
 *              transferred.
 *
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$loader = new Composer\Autoload\ClassLoader();
$loader->addPsr4('Incasso\\', __DIR__ . '/../addons/incasso/lib/incasso');
$loader->addPsr4('IsoCodes\\', __DIR__ . '/../addons/incasso/lib/isocodes');
$loader->register();

use Illuminate\Database\Capsule\Manager as Capsule;
use Incasso\Models\Mandate;
use Incasso\Models\Client;
use Incasso\Models\Setting;
use Incasso\Core\Helper;
use Incasso\Validators\MandateValidator;
use Incasso\Controllers\ClientController;

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities
 * and settings.
 *
 * @return array
 */
function sepaincasso_MetaData() {
    return array(
        'DisplayName' => 'Incasso / SEPA direct debit',
        'APIVersion' => '1.1',
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * @return array
 */
function sepaincasso_config() {
    return array(
        'FriendlyName' => array(
            'Type'  => 'System',
            'Value' => 'Incasso / SEPA direct debit',
        ),
    );
}

/**
 * Payment link
 *
 * This generally displays a link to perform a payment but we use
 * it to explain to the customer how to configure direct debits.
 *
 * @return string
 */
function sepaincasso_link($params) {
    global $_POST, $_SESSION;

    $smarty = new Smarty();
    $mandate = new Mandate();
    $validator = new MandateValidator();
    $settings = Setting::getSettings();

    // Set some locations to our module and where to find the language files
    $module_location = __DIR__ . '/../addons/incasso';
    $language_location = __DIR__ . '/../addons/incasso/lang';
    $systemurl = $params['systemurl'];

    // Include our language file and default to English if no suitable language is found
    if ($_SESSION['Language']) {
        $language = $_SESSION['Language'];
    } else {
        $language = $params['clientdetails']['language'];
    }

    if (file_exists("${language_location}/${language}.php")) {
        include("${language_location}/${language}.php");
    } else {
        if(file_exists("${language_location}/english.php")) {
            include("${language_location}/english.php");
        } else {
            // If no files can't be found at all it means that the addon module isn't available
            return 'Sorry, this payment module cannot be used yet. Please contact us for more details.';
        }
    }

    $clientsdetails = localAPI('GetClientsDetails', array('clientid' => $_SESSION['uid'], 'stats' => true), $settings->api_user);
    $customfields = Helper::customFieldsToArray($clientsdetails['client']['customfields']);

    // Set up variables
    $mandate->customer_name = $customfields[$settings->bankholder];
    $mandate->customer_bankaccount_city = $customfields[$settings->bankcity];
    $mandate->customer_bankaccount_number = $customfields[$settings->bankno];
    $mandate->customer_bankaccount_bic = $customfields[$settings->bicno];

    // Validate our variables
    if (is_array($validation = $mandate->validates()) OR $validator->validateSignatureDate($customfields[$settings->customer_mandate]) == false) {
        $payment_link = true;
    } else {
        $payment_link = false;
    }

    // Output template
    $smarty->assign('payment_link', $payment_link);
    $smarty->assign('mandate', $mandate);
    $smarty->assign('language', $_ADDONLANG);
    $smarty->assign('systemurl', $systemurl);
    $output = $smarty->fetch($module_location . '/templates/gateway/payment.tpl');
    return $output;
}

?>

<?php

namespace Incasso\Controllers\Client\Mandates;

use Incasso\Models\Mandate;
use Incasso\Models\Client;
use Incasso\Models\Setting;
use Incasso\Core\Helper;
use Incasso\Validators\MandateValidator;
use Incasso\Controllers\ClientController;
use Illuminate\Database\Capsule\Manager as Capsule;

class SignMandateController extends ClientController {
    protected $template = 'clientarea/sign_mandate';

    public function get($smarty, $vars) {
        $mandate = new Mandate();
        $validator = new MandateValidator();
        $settings = Setting::getSettings();

        // Set a title and breadcrumb
        $this->title = $vars['_lang']['menu_sign'];
        $this->breadcrumb['index.php?m=incasso&amp;action=sign'] = $vars['_lang']['menu_sign'];

        // Get client details
        $clientsdetails = localAPI('GetClientsDetails', array('clientid' => $_SESSION['uid'], 'stats' => true), $settings->api_user);
        $customfields = Helper::customFieldsToArray($clientsdetails['client']['customfields']);

        // Set our variables
        $this->vars['customer_signature_date'] = date('Y-m-d');
        $this->vars['mandate_reference'] = $mandate->generateMandateReference($_SESSION['uid'], $customfields[$settings->customer_manref]);

        // Display the form is we received a post request
        if ($_POST) {
            $display_form = true;
        } else {
            // Verify if the data we have is accurate - if it is notify the customer that it isn't possible to sign another mandate
            $mandate->customer_name = $customfields[$settings->bankholder];
            $mandate->customer_bankaccount_city = $customfields[$settings->bankcity];
            $mandate->customer_bankaccount_number = $customfields[$settings->bankno];
            $mandate->customer_bankaccount_bic = $customfields[$settings->bicno];

            if (is_array($validation = $mandate->validates()) OR $validator->validateSignatureDate($customfields[$settings->customer_mandate]) == false) {
                $this->vars['customer_name'] = $mandate->customer_name;
                $this->vars['customer_bankaccount_city'] = $mandate->customer_bankaccount_city;
                $this->vars['customer_bankaccount_number'] = $mandate->customer_bankaccount_number;
                $this->vars['customer_bankaccount_bic'] = $mandate->customer_bankaccount_bic;
                $display_form = true;
            } else {
                $display_form = false;
            }
        }

        // Return the page
        $this->vars['display_form'] = $display_form;
        return parent::get($smarty, $vars);
    }

    public function post($smarty, $vars) {
        $mandate = new Mandate();
        $settings = Setting::getSettings();
        $client = array('clientid' => $_SESSION['uid']);

        // Get the current list of customfields. We need to save them all.
        $clientsdetails = localAPI('GetClientsDetails', array('clientid' => $_SESSION['uid'], 'stats' => true), $settings->api_user);
        $customfields = Helper::customFieldsToArray($clientsdetails['client']['customfields']);

        // Set the default payment method and a smarty variable in case the form fails
        if (isset($_POST['change_default_payment_method'])) {
            $this->vars['change_default_payment_method'] = true;
            $client['paymentmethod'] = 'sepaincasso';
        }

        // Make sure we're within a transaction and save the mandate
        Capsule::beginTransaction();
        $result = $mandate->saveMandate($_POST);

        // If anything goes wrong set some variables
        if (is_array($result) OR $result == false) {
            $this->vars['errors'] = $result;
            $this->vars['customer_name'] = htmlentities($_POST['customer_name']);
            $this->vars['customer_bankaccount_city'] = htmlentities($_POST['customer_bankaccount_city']);
            $this->vars['customer_bankaccount_number'] = htmlentities($_POST['customer_bankaccount_number']);
            $this->vars['customer_bankaccount_bic'] = htmlentities($_POST['customer_bankaccount_bic']);

            logModuleCall('incasso', 'SignMandateController.post', '', $result, '', array());
        } else {
            $this->vars['mandate_id'] = $result;

            // Save these settings to custom fields
            $customfields[$settings->bankholder]       = $_POST['customer_name'];
            $customfields[$settings->bankno]           = $_POST['customer_bankaccount_number'];
            $customfields[$settings->bicno]            = $_POST['customer_bankaccount_bic'];
            $customfields[$settings->customer_manref]  = $_POST['mandate_reference'];
            $customfields[$settings->customer_mandate] = $_POST['customer_signature_date'];

            if ($settings['bankcity'] != 9999) {
                $customfields[$settings['bankcity']] = $_POST['customer_bankaccount_city'];
            }

            // Update the client
            $client['customfields'] = base64_encode(serialize($customfields));
            $result = localAPI('UpdateClient', $client, $settings->api_user);

            if ($result['result'] != 'success') {
                $this->vars['errors'] = $result;
                $this->vars['customer_name'] = htmlentities($_POST['customer_name']);
                $this->vars['customer_bankaccount_city'] = htmlentities($_POST['customer_bankaccount_city']);
                $this->vars['customer_bankaccount_number'] = htmlentities($_POST['customer_bankaccount_number']);
                $this->vars['customer_bankaccount_bic'] = htmlentities($_POST['customer_bankaccount_bic']);

                logModuleCall('incasso', 'SignMandateController.post', '', $result, '', array());
            } else {
                Capsule::commit();
                unset($_POST);
                header('Location: index.php?m=incasso&page=mandates&action=view&id=' . $this->vars['mandate_id']);
                die();
            }
        }

        // If we get to this point something has failed, roll back
        Capsule::rollBack();
        return self::get($smarty, $vars);
    }
}

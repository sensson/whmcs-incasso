<?php

namespace Incasso\Models;

use Incasso\Models\Setting;
use Illuminate\Database\Capsule\Manager as Capsule;

class Mandate extends BaseModel {
    public $table = 'mod_incasso_mandates';
    public $primaryKey = 'uid';
    protected $validator_class = 'Incasso\\Validators\\MandateValidator';

    public function generateMandateReference($customer_id, $current_mandate_reference) {
        if (strlen($current_mandate_reference) == 0) {
            return "CID-{$customer_id}";
        } else {
            return $current_mandate_reference;
        }
    }

    public function getMandates($filter = array()) {
        $mandates = Mandate::select(
                'uid',
                'timestamp',
                'company_name',
                'mandate_reference',
                'customer_name',
                'customer_id'
            )->orderby('uid');

        if (!empty($filter)) {
            if (array_key_exists('mandate_reference', $filter)) {
                if (strlen($filter['mandate_reference']) != 0) {
                    $mandates->where('mandate_reference', 'like', '%' . (string) $filter['mandate_reference']);
                }
            }

            if (array_key_exists('customer_id', $filter)) {
                if (strlen($filter['customer_id']) != 0) {
                    $mandates->where('customer_id', '=', (int) $filter['customer_id']);
                }
            }
        }

        $mandates = $mandates->get();
        return $mandates;
    }

    public function getMandate($uid, $filter = array()) {
        $mandate = Mandate::where('uid', '=', $uid);

        if (!empty($filter)) {
            if (array_key_exists('customer_id', $filter)) {
                $mandate->where('customer_id', '=', (int) $filter['customer_id']);
            }
        }

        // Get only active mandates
        // $mandate->where('status', '=', 'active');

        return $mandate->first();
    }

    public function saveMandate($data) {
        $settings = Setting::getSettings();
        $clientsdetails = Capsule::table('tblclients')->select()->where('id', '=', $_SESSION['uid'])->first();

        $mandate = new Mandate();
        $mandate->timestamp = time();
        $mandate->ipaddress = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

        // This is a little off at the moment..
        $mandate->company_name                  = $settings->mybankholder;
        $mandate->company_address               = $settings->myaddress;
        $mandate->company_postcode              = $settings->mypostcode;
        $mandate->company_city                  = $settings->mycity;
        $mandate->company_country               = $settings->mycountry;
        $mandate->company_creditor_id           = $settings->mycreditorid;

        // Customer details
        $mandate->mandate_reference             = $data['mandate_reference'];
        $mandate->mandate_description           = $settings->myfixeddesc;

        $mandate->customer_id                   = $_SESSION['uid'];
        $mandate->customer_name                 = $data['customer_name'];
        $mandate->customer_address              = $clientsdetails->address1;
        $mandate->customer_postcode             = $clientsdetails->postcode;
        $mandate->customer_city                 = $clientsdetails->city;
        $mandate->customer_country              = $clientsdetails->country;
        $mandate->customer_bankaccount_number   = $data['customer_bankaccount_number'];
        $mandate->customer_bankaccount_bic      = $data['customer_bankaccount_bic'];
        $mandate->customer_bankaccount_city     = $clientsdetails->city;
        $mandate->customer_signature            = $data['signature'];
        $mandate->customer_signature_date       = $data['customer_signature_date'];

        // And only when required
        if ($settings->bankcity != 9999) {
            $mandate->customer_bankaccount_city = $data['customer_bankaccount_city'];
        }

        if (!is_array($validation = $mandate->validates())) {
            try {
                $mandate->save();
                return $mandate->uid;
            } catch (\Exception $exception) {
                logModuleCall('incasso', 'createMandate', '', $exception->getMessage(), '', array());
                return false;
            }
        } else {
            return $validation;
        }
    }
}

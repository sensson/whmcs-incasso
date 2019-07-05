<?php

namespace Incasso\Validators;

use IsoCodes\Iban;
use IsoCodes\SwiftBic;
use Incasso\Models\Setting;

class MandateValidator extends Validator {

    public function validateBankAccountHolder($customer_name) {
        if (strlen($customer_name) == 0) {
            $this->addError('customer_name', 'is not a valid name (empty)');
        }
    }

    public function validateBankAccountCity($customer_bankaccount_city) {
        if (strlen($customer_bankaccount_city) == 0) {
            $this->addError('customer_bankaccount_city', 'is not a valid city (empty)');
        }
    }

    public function validateBankAccountIBAN($customer_bankaccount_number) {
        if (!Iban::validate($customer_bankaccount_number)) {
            $this->addError('customer_bankaccount_number', 'is not a valid IBAN number');
        }
    }

    public function validateBankAccountBIC($customer_bankaccount_bic) {
        if (!SwiftBic::validate($customer_bankaccount_bic)) {
            $this->addError('customer_bankaccount_bic', 'is not a valid BIC number');
        }
    }

    public function validateSignatureDate($customer_signature_date) {
        if (!preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $customer_signature_date)) {
            $this->addError('customer_signature_date', 'is not a valid date');
            return false;
        }
        return true;
    }

    public function validate($data) {
        $settings = Setting::getSettings();

        // Check if there are no other active mandates, because if there are, they may have submitted the form twice
        $this->validateBankAccountHolder($data->customer_name);
        $this->validateBankAccountIBAN($data->customer_bankaccount_number);
        $this->validateBankAccountBIC($data->customer_bankaccount_bic);

        if ($settings['bankcity'] != 9999) {
            $this->validateBankAccountCity($data->customer_bankaccount_city);
        }

        return parent::validate($data);
    }

}

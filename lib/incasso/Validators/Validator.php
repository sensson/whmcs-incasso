<?php

namespace Incasso\Validators;

abstract class Validator {
    protected $validation_errors = array();

    public function addError($name, $error) {
        $this->validation_errors[$name] = $error;
    }

    public function getErrors() {
        return $this->validation_errors;
    }

    public function resetErrors() {
        $this->validation_errors = array();
    }

    public function validate($data) {
        if (!empty($this->getErrors())) {
            return $this->getErrors();
        }

        return true;
    }
}

<?php

namespace Incasso\Controllers\Admin\Mandates;

use Incasso\Models\Mandate;
use Incasso\Controllers\Controller;

class ListMandatesController extends Controller {
    protected $template = 'mandates/list_mandates.tpl';
    protected $filter = array();

    public function get($smarty, $vars) {
        $mandates = new Mandate();
        $smarty->assign('mandates', $mandates->getMandates($this->filter));
        return parent::get($smarty, $vars);
    }

    public function post($smarty, $vars) {
        // Filter on mandate reference
        if (array_key_exists('mandate_reference', $_POST)) {
            $this->filter['mandate_reference'] = $_POST['mandate_reference'];
            $smarty->assign('mandate_reference', htmlentities($_POST['mandate_reference']));
        }

        // Filter on customer id
        if (array_key_exists('customer_id', $_POST)) {
            $this->filter['customer_id'] = $_POST['customer_id'];
        }

        // Only set a filter
        return $this->get($smarty, $vars);
    }
}

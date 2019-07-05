<?php

namespace Incasso\Controllers\Client\Mandates;

use Incasso\Models\Mandate;
use Incasso\Controllers\ClientController;

class ListMandatesController extends ClientController {
    protected $template = 'clientarea/list_mandates';
    protected $vars = array();

    public function get($smarty, $vars) {
        $mandate = new Mandate();

        $filter['customer_id'] = $_SESSION['uid'];
        $this->vars['mandates'] = $mandate->getMandates($filter);

        // Set the title of this page
        $this->title = $vars['_lang']['direct_debit'];

        return parent::get($smarty, $vars);
    }
}

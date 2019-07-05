<?php

namespace Incasso\Controllers\Client\Mandates;

use Incasso\Models\Mandate;
use Incasso\Controllers\ClientController;

class ViewMandateController extends ClientController {
    protected $template = 'clientarea/view_mandate';

    public function get($smarty, $vars) {
        $mandates = new Mandate();
        $filter['customer_id'] = $_SESSION['uid'];
        $this->vars['mandate'] = $mandates->getMandate($_GET['id'], $filter);
        $this->title = $this->vars['mandate']->mandate_reference;
        $this->breadcrumb['index.php?m=incasso&amp;action=view&id=' . $this->vars['mandate']->uid] = $this->vars['mandate']->mandate_reference;

        return parent::get($smarty, $vars);
    }
}

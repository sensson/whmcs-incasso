<?php

namespace Incasso\Controllers\Admin\Mandates;

use Incasso\Models\Mandate;
use Incasso\Controllers\Controller;

class ViewMandateController extends Controller {
    protected $template = 'mandates/view_mandate.tpl';

    public function get($smarty, $vars) {
        $id = $_GET['id'];
        $mandate = new Mandate();
        $smarty->assign('mandate', $mandate->getMandate($id));

        return parent::get($smarty, $vars);
    }
}

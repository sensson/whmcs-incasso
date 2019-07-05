<?php

namespace Incasso\Controllers;

use Incasso\Models\Setting;
use WHMCS\View\Menu\MenuFactory;

abstract class ClientController extends Controller {
    protected $template = 'base';
    protected $vars = array();
    protected $requirelogin = true;
    protected $forcessl = false;
    protected $title = 'Incasso';
    protected $breadcrumb = array('index.php?m=incasso' => 'Incasso');

    public function get($smarty, $vars) {
        $this->vars['modulelink'] = $vars['modulelink'];
        $this->vars['addon_lang'] = $vars['_lang'];
        $this->vars['actions'] = $vars['_actions'];
        $this->vars['settings'] = Setting::getSettings();

        // Make sure there's always a title set
        if (strlen($this->title) == 0) {
            $this->title = $vars['_lang']['direct_debit'];
        }

        // Override the first breadcrumb to make sure it translates properly
        $this->breadcrumb['index.php?m=incasso'] = $vars['_lang']['direct_debit'];

        // The client functionality in WHMCS is different to the admin side
        return array(
            'pagetitle' => $this->title,
            'breadcrumb' => $this->breadcrumb,
            'templatefile' => $this->template,
            'requirelogin' => $this->requirelogin,
            'forcessl' => $this->forcessl,
            'vars' => $this->vars,
        );
    }

    public function post($smarty, $vars) {
        return $this->get($vars);
    }
}

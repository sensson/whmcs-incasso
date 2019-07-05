<?php

namespace Incasso\Controllers\Client;

use Incasso\Models\Mandates;
use Incasso\Controllers\ActionController;

class MandatesController extends ActionController {
    protected $actions = array(
        'list' => array(
            'class' => 'Incasso\\Controllers\\Client\\Mandates\\ListMandatesController',
            'description' => 'Show the mandate overview',
            'menu' => true,
            'order' => 1,
        ),
        'view' => array(
            'class' => 'Incasso\\Controllers\\Client\\Mandates\\ViewMandateController',
            'description' => 'View a specific mandate',
            'menu' => false,
        ),
        'sign' => array(
            'class' => 'Incasso\\Controllers\\Client\\Mandates\\SignMandateController',
            'description' => 'Sign a new mandate',
            'menu' => true,
            'order' => 2,
        ),
    );

    public function get($smarty, $vars) {
        $controller = $this->getActionController($_GET['action']);
        $vars['_actions'] = $this->getActions();
        $vars['_action_name'] = $this->getActionName($_GET['action']);
        return $controller->get($smarty, $vars);
    }

    public function post($smarty, $vars) {
        $controller = $this->getActionController($_GET['action']);
        return $controller->post($smarty, $vars);
    }
}

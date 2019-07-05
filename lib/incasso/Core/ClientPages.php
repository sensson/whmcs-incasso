<?php

namespace Incasso\Core;

class ClientPages extends Pages {
    protected $default = 'mandates';
    protected $pages = array(
        'mandates' => array(
            'class' => 'Incasso\\Controllers\\Client\\MandatesController',
            'description' => 'Mandate management for clients',
            'type' => 'page',
        ),
    );

    public function get($name, $smarty, $vars) {
        $controller = $this->getController($name);
        return $controller->get($smarty, $vars);
    }

    public function post($name, $smarty, $vars) {
        $controller = $this->getController($name);
        return $controller->post($smarty, $vars);
    }
}

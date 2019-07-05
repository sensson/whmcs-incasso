<?php

namespace Incasso\Core;

class AdminPages extends Pages {
    protected $default = 'home';
    protected $pages = array(
        'mandates' => array(
            'class' => 'Incasso\\Controllers\\Admin\\MandatesController',
            'description' => 'Mandate management',
            'type' => 'page',
        ),
    );
}

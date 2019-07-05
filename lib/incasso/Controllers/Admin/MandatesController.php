<?php

namespace Incasso\Controllers\Admin;

use Incasso\Controllers\ActionController;

class MandatesController extends ActionController {
    protected $actions = array(
        'list' => array(
            'class' => 'Incasso\\Controllers\\Admin\\Mandates\\ListMandatesController',
            'description' => 'Show the mandate overview',
        ),
        'view' => array(
            'class' => 'Incasso\\Controllers\\Admin\\Mandates\\ViewMandateController',
            'description' => 'View a specific mandate',
        )
    );
}

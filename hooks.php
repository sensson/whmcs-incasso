<?php
/**
 * Incasso direct debit module
 *
 * This module allows direct debits based on the SEPA standard. It's only requirement
 * is that you have a contract with your bank to perform direct debits.
 *
 * Support is available via info@sensson.net.
 *
 * @author      Sensson <info@sensson.net>
 * @copyright   2004-2016 Sensson
 * @license     This software is furnished under a license and may be used and copied
 *              only  in  accordance  with  the  terms  of such  license and with the
 *              inclusion of the above copyright notice.  This software  or any other
 *              copies thereof may not be provided or otherwise made available to any
 *              other person.  No title to and  ownership of the  software is  hereby
 *              transferred.
 *
 */

// Load our namespace
$loader = new Composer\Autoload\ClassLoader();
$loader->addPsr4('Incasso\\', __DIR__ . '/lib/incasso');
$loader->addPsr4('IsoCodes\\', __DIR__ . '/lib/isocodes');
$loader->register();

use WHMCS\View\Menu\Item;
use Illuminate\Database\Capsule\Manager as Capsule;
use Incasso\Controllers\Client\MandatesController;

add_hook('ClientAreaPrimarySidebar', -1, function ($sidebar) {
    // This isn't very DRY but we have no other choice unfortunately
    $lang = $_SESSION['Language'];
    $lang_directory = __DIR__ . '/lang';
    if(file_exists("{$lang_directory}/{$lang}.php")) {
        include("{$lang_directory}/{$lang}.php");
    } else {
        include("{$lang_directory}/english.php");
    }

    // We may want to change this in the future as it can only control mandates at the moment
    $controller = new MandatesController();

    if ($_GET['m'] == 'incasso') {
        $menu = $sidebar->addChild(
            $_ADDONLANG['direct_debit'],
                array (
                    'attributes' => array(
                    'class' => 'panel-default panel-actions'
                ),
            )
        );

        foreach ($controller->getActions() as $action_name => $action) {
            if ($action['menu'] == true) {
                $item = $menu->addChild($_ADDONLANG['menu_' . $action_name],
                    array(
                        'uri' => 'index.php?m=incasso&amp;action=' . $action_name,
                        'order' => $action['order'],
                    )
                );

                if ($action_name == $controller->getActionName($_GET['action'])) {
                    $item->setClass('active');
                }
            }
        }
    }
});

add_hook('ClientAreaSecondaryNavbar', 1, function (Item $secondaryNavbar) {
    // This isn't very DRY but we have no other choice unfortunately
    $lang = $_SESSION['Language'];
    $lang_directory = __DIR__ . '/lang';
    if(file_exists("{$lang_directory}/{$lang}.php")) {
        include("{$lang_directory}/{$lang}.php");
    } else {
        include("{$lang_directory}/english.php");
    }

    if ($_SESSION['uid']) {
        if (!is_null($secondaryNavbar->getChild('Account'))) {
            $secondaryNavbar->getChild('Account')->addChild($_ADDONLANG['direct_debit'], array(
                    'label' => Lang::trans('directDebitHeader'),
                    'uri' => 'index.php?m=incasso',
                    'order' => '12',
                )
            );
        }
    }
});

add_hook('ClientAreaHomepagePanels', 1, function (Item $homePagePanels) {
    // This isn't very DRY but we have no other choice unfortunately
    $lang = $_SESSION['Language'];
    $lang_directory = __DIR__ . '/lang';
    if(file_exists("{$lang_directory}/{$lang}.php")) {
        include("{$lang_directory}/{$lang}.php");
    } else {
        include("{$lang_directory}/english.php");
    }

    // List all upcoming direct debits that have not been paid yet
    $directDebitInvoices = Capsule::table('mod_incasso_batch')
                ->join('tblinvoices', 'tblinvoices.id', '=', 'mod_incasso_batch.invoice_id')
                ->select(
                    'mod_incasso_batch.invoice_id',
                    'mod_incasso_batch.batch_type',
                    'mod_incasso_batch.date_frst',
                    'mod_incasso_batch.date_rcur',
                    'tblinvoices.invoicenum',
                    'tblinvoices.id',
                    'tblinvoices.total'
                )
                ->where('mod_incasso_batch.client_id', '=', $_SESSION['uid'])
                ->where('tblinvoices.status', '=', 'Unpaid');

    $previous_payments = Capsule::table('tblaccounts')->where('userid', '=', $user_id);
    $previous_payments->where(function ($query) use ($gateway, $legacy_gateway) {
        $query->where('gateway', '=', $gateway);

        if ($legacy_gateway != '') {
            $query->orWhere('gateway', '=', $legacy_gateway);
        }
    });
    $previous_payments->count();

    // If there are any direct debits scheduled we will add a new panel
    if($directDebitInvoices->count() > 0) {
        $total = (int) 0;
        $invoices = $directDebitInvoices->get();

        // Create a list of scheduled invoices
        $bodyHtml = '<div class="custom-list-group">';
        foreach($invoices as $invoice) {
            // Check what invoice number to display
            if($invoice->invoicenum == '') {
                $invoicenum = $invoice->id;
            } else {
                $invoicenum = $invoice->invoicenum;
            }

            // Select date based on RCUR or FRST
            if($previous_payments >= 1 AND $invoice->batch_type == 0) {
                $due_date = date('d-m-Y', $invoice->date_rcur);
            } else {
                $due_date = date('d-m-Y', $invoice->date_frst);
            }
            if($invoice->batch_type == 1) {
                $due_date = date('d-m-Y', $invoice->date_frst);
            }
            if($invoice->batch_type == 2) {
                $due_date = date('d-m-Y', $invoice->date_rcur);
            }

            // Increase totals
            $total = $total + $invoice->total;

            // Set up our content
            $bodyHtml .= "<div class='list-group-item'>
                            <span class='invoice-text'><a href='viewinvoice.php?id={$invoice->id}'>{$invoicenum}</a></span>
                            <span class='invoice-scheduled'>" . $_ADDONLANG['scheduled_on'] . " {$due_date}</span>
                            <span class='invoice-due'>&nbsp;&euro;{$invoice->total}</span>
                          </div>";
        }
        $bodyHtml .= '</div>';

        // Add the home page panel
        $scheduledDebitPanel = $homePagePanels->addChild('Upcoming direct debits', array(
                'label' => $_ADDONLANG['scheduled_direct_debits'],
                'icon' => 'fa-credit-card',
                'bodyHtml' => $bodyHtml,
                'footerHtml' => $_ADDONLANG['total_scheduled'] . "&nbsp;&euro;{$total}.<style>.custom-list-group > .list-group-item { border-width: 1px 0; border-radius: 0;}</style>"
            ));
    }
});
?>

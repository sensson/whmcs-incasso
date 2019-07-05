<?php

namespace Incasso\Core;
use Illuminate\Database\Capsule\Manager as Capsule;

class Helper {
    public static function customFieldsToArray($customfields) {
        $customfields_array = array();
        foreach ($customfields as $customfield) {
            $customfields_array[$customfield['id']] = $customfield['value'];
        }
        return $customfields_array;
    }

    public static function frstOrRcur($customer_id, $mandate_reference = '', $gateways = array()) {
        // Select the last transactions
        $transactions = Capsule::table('tblaccounts')->where('userid', '=', (int) $customer_id);
        $transactions->where(function ($query) use ($gateways) {
            if (!empty($gateways)) {
                foreach ($gateways as $gateway) {
                    $query->orWhere('gateway', '=', $gateway);
                }
            }
        });
        $transactions->orderBy('tblaccounts.id', 'desc');
        $transactions->take(1);

        if ($transactions->count() > 0) {
            $transactions = $transactions->get();

            foreach ($transactions as $key => $transaction) {
                // This has an edge case that we can't solve:
                //   If the customer only paid for invoices using the old standard
                //   and they have switched bank accounts which resulted in a changed mandate
                //   then we will never be able to find it - just as we couldn't find it before
                if (preg_match('/T[0-9]+C[0-9]+I[0-9]+$/', $transaction->transid)) {
                    return 'RCUR';
                } elseif (preg_match('/T[0-9]+C[0-9]+I[0-9]+M(.*)$/', $transaction->transid)) {
                    $previous_mandate_reference = preg_replace('/T[0-9]+C[0-9]+I[0-9]+M/', '', $transaction->transid);

                    if ($previous_mandate_reference == $mandate_reference) {
                        return 'RCUR';
                    } else {
                        return 'FRST';
                    }
                } else {
                    return 'FRST';
                }
            }
        }
    }

    public static function getAdmins() {
        $admins = array();

        foreach (Capsule::table('tbladmins')->select('username')->get() as $admin) {
            $admins[$admin->username] = $admin->username;
        }
        return $admins;
    }
}

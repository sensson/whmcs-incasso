<?php

namespace Incasso\Models;

use Illuminate\Database\Capsule\Manager as Capsule;

class Setting extends BaseModel {
    public $table = 'mod_incasso';

    public static function getSettings() {
        $raw_settings = Capsule::table('mod_incasso')->select('configname', 'configvalue')->get();

        $settings = new Setting();
        foreach ($raw_settings as $key => $setting) {
            $settings->{$setting->configname} = $setting->configvalue;
        }

        return $settings;
    }

    public function getSetting($key) {
        return Setting::where('configname', '=', $key)->first()['attributes']['configvalue'];
    }

    public function saveSetting($configname, $configvalue) {
        $setting = new Setting();
        $setting->configname = $configname;
        $setting->configvalue = $configvalue;
        $setting->save();
        return true;
    }
}

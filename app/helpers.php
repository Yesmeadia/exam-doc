<?php

date_default_timezone_set('Asia/Kolkata');

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get / set the specified setting value.
     *
     * If an array is passed as the key, we assume you want to set an array of values.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed|\App\Models\Setting
     */
    function setting(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return Setting::getAllSettings();
        }

        return Setting::get($key, $default);
    }
}

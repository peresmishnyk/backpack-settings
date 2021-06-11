<?php

if (!function_exists('backpack_settings_url')) {
    function backpack_settings_url(string $name)
    {
        return route('settings.' . $name . '.edit');
    }
}

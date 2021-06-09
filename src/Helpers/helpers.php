<?php

if (!function_exists('backpack_settings_url')) {
    function backpack_settings_url(string $key){
        return backpack_url(Settings::config('route_prefix').'/' . $key . '/edit');
    }
}

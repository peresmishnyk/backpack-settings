<?php


namespace Peresmishnyk\BackpackSettings;


class Settings
{
    protected $config_key;

    public function __construct($config_key)
    {
        $this->config_key = $config_key;
    }

    public function config($key=''){
        return config($key == '' ? $this->config_key : $this->config_key . '.' .$key);
    }
}

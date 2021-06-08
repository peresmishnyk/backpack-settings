<?php

namespace Peresmishnyk\BackpackSettings\Facades;

use Illuminate\Support\Facades\Facade;

class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'settings';
    }
}

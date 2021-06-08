<?php

namespace Peresmishnyk\BackpackSetting;

use Illuminate\Support\ServiceProvider;
use Peresmishnyk\BackpackSetting\Commands\SettingInstallCommand;

class AddonServiceProvider extends ServiceProvider
{
    use AutomaticServiceProvider;

    protected $vendorName = 'peresmishnyk';
    protected $packageName = 'backpack-settings';
    protected $commands = [
        SettingInstallCommand::class
    ];
}

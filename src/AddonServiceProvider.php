<?php

namespace Peresmishnyk\BackpackSettings;

use Illuminate\Support\ServiceProvider;
use Peresmishnyk\BackpackSettings\Commands\SettingsInstallCommand;

class AddonServiceProvider extends ServiceProvider
{
    use AutomaticServiceProvider;

    protected $vendorName = 'peresmishnyk';
    protected $packageName = 'backpack-settings';
    protected $commands = [
        SettingsInstallCommand::class
    ];

}

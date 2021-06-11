<?php

namespace Peresmishnyk\BackpackSettings\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SettingsBackpackCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backpack:settings {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a CRUD interface: Controller, Model, Request';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = ucfirst($this->argument('name'));
        $lowerName = strtolower($this->argument('name'));
        $pluralName = Str::plural($name);

        // Create the CRUD Controller and show output
        $this->call('settings:controller', ['name' => $name]);

        // Create the CRUD Request and show output
        $this->call('settings:request', ['name' => $name]);

        // Create the CRUD route
        $this->call('settings:add-custom-route', [
            'code' => "Route::settings('$lowerName', '{$name}SettingsController');",
        ]);

        // Create the sidebar item
        $this->call('backpack:add-sidebar-content', [
            'code' => "<li class='nav-item'><a class='nav-link' href='{{ backpack_settings_url('$lowerName') }}'><i class='nav-icon la la-cog'></i> $pluralName</a></li>",
        ]);

        // if the application uses cached routes, we should rebuild the cache so the previous added route will
        // be acessible without manually clearing the route cache.
        if (app()->routesAreCached()) {
            $this->call('route:cache');
        }
    }
}

<?php

namespace Peresmishnyk\BackpackSettings;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Peresmishnyk\BackpackSettings\Commands\SettingsBackpackCommand;
use Peresmishnyk\BackpackSettings\Commands\SettingsControllerBackpackCommand;
use Peresmishnyk\BackpackSettings\Commands\SettingsInstallCommand;

class BackpackSettingsServiceProvider extends ServiceProvider
{
    use AutomaticServiceProvider;

    protected $vendorName = 'peresmishnyk';
    protected $packageName = 'backpack-settings';

    // Where custom routes can be written, and will be registered by Backpack Settings.
    public $customRoutesFilePath = '/routes/backpack/settings.php';

    protected $commands = [
        SettingsInstallCommand::class,
        SettingsControllerBackpackCommand::class,
        SettingsBackpackCommand::class,
    ];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->packageDirectoryExistsAndIsNotEmpty('bootstrap') &&
            file_exists($helpers = $this->packageHelpersFile())) {
            require $helpers;
        }

        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/lang')) {
            $this->loadTranslationsFrom($this->packageLangsPath(), $this->vendorNameDotPackageName());
        }

        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/views')) {
            // Load published views
            $this->loadViewsFrom($this->publishedViewsPath(), $this->vendorNameDotPackageName());

            // Fallback to package views
            $this->loadViewsFrom($this->packageViewsPath(), $this->vendorNameDotPackageName());
        }

        if ($this->packageDirectoryExistsAndIsNotEmpty('database/migrations')) {
            $this->loadMigrationsFrom($this->packageMigrationsPath());
        }

        if ($this->packageDirectoryExistsAndIsNotEmpty('routes')) {
            $this->loadRoutesFrom($this->packageRoutesFile());
        }

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $this->setupCustomRoutes($this->app->router);
        $this->configOverride();
    }

    /**
     * Load custom routes file.
     *
     * @param \Illuminate\Routing\Router $router
     *
     * @return void
     */
    public function setupCustomRoutes(Router $router)
    {
        // if the custom routes file is published, register its routes
        if (file_exists(base_path().$this->customRoutesFilePath)) {
            $this->loadRoutesFrom(base_path().$this->customRoutesFilePath);
        }
    }

    protected function publishedConfigFile()
    {
        return config_path('backpack/settings.php');
    }

    private function configOverride()
    {
        $overrides = \Settings::config('config_override');
        foreach ($overrides as $config_key => $settings_key) {
            Config::set($config_key, \Settings::get($settings_key, Config::get($config_key)));
        }
    }

}

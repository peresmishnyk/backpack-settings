<?php

namespace Peresmishnyk\BackpackSettings;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Peresmishnyk\BackpackSettings\Commands\SettingsAddCustomRouteContent;
use Peresmishnyk\BackpackSettings\Commands\SettingsBackpackCommand;
use Peresmishnyk\BackpackSettings\Commands\SettingsControllerBackpackCommand;
use Peresmishnyk\BackpackSettings\Commands\SettingsInstallCommand;
use Peresmishnyk\BackpackSettings\Commands\SettingsRequestBackpackCommand;

class BackpackSettingsServiceProvider extends ServiceProvider
{
    use AutomaticServiceProvider;

    protected $vendorName = 'peresmishnyk';
    protected $packageName = 'backpack-settings';

    // Where custom routes can be written, and will be registered by Backpack Settings.
    public $customRoutesFilePath = '/routes/backpack/settings.php';

    protected $commands = [
        SettingsInstallCommand::class,
        SettingsBackpackCommand::class,
        SettingsControllerBackpackCommand::class,
        SettingsRequestBackpackCommand::class,
        SettingsAddCustomRouteContent::class,
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

//        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/views')) {
//            // Load published views
//            //$this->loadViewsFrom($this->publishedViewsPath(), $this->vendorNameDotPackageName());
//
//            // Fallback to package views
//            //$this->loadViewsFrom($this->packageViewsPath(), $this->vendorNameDotPackageName());
//        }

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
//        $this->configOverride();
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
        $overrides = app('settings')->config('config_override');
        foreach ($overrides as $config_key => $settings_key) {
            Config::set($config_key, \Settings::get($settings_key, Config::get($config_key)));
        }
    }

    private function addRouteMacro()
    {
        Route::macro('settings', function ($name, $controller) {
            // put together the route name prefix,
            // as passed to the Route::group() statements
            $routeName = '';
            if ($this->hasGroupStack()) {
                foreach ($this->getGroupStack() as $key => $groupStack) {
                    if (isset($groupStack['name'])) {
                        if (is_array($groupStack['name'])) {
                            $routeName = implode('', $groupStack['name']);
                        } else {
                            $routeName = $groupStack['name'];
                        }
                    }
                }
            }
            // add the name of the current entity to the route name prefix
            // the result will be the current route name (not ending in dot)
            $routeName = $routeName . $name;

            // get an instance of the controller
            if ($this->hasGroupStack()) {
                $groupStack = $this->getGroupStack();
                $groupNamespace = $groupStack && isset(end($groupStack)['namespace']) ? end($groupStack)['namespace'] . '\\' : '';
            } else {
                $groupNamespace = '';
            }
            $namespacedController = $groupNamespace . $controller;
            $controllerInstance = App::make($namespacedController);

            return $controllerInstance->setupRoutes(\Settings::config('route_prefix'), $routeName, $controller);
        });
    }

}

<?php

namespace Peresmishnyk\BackpackSettings;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/**
 * This trait automatically loads package stuff, if they're present
 * in the expected directory. Stick to the conventions and
 * your package will "just work". Feel free to override
 * any of the methods below in your ServiceProvider
 * if you need to change the paths.
 */
trait AutomaticServiceProvider
{
    public function __construct($app)
    {
        $this->app = $app;
        $this->path = __DIR__ . '/..';
    }

    /**
     * -------------------------
     * SERVICE PROVIDER DEFAULTS
     * -------------------------
     */

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

        $this->configOverride();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->packageDirectoryExistsAndIsNotEmpty('config')) {
            $this->mergeConfigFrom($this->packageConfigFile(), $this->vendorNameDotPackageName());
        }

        $this->app->singleton('settings', function ($app) {
            return new Settings($this->vendorNameDotPackageName());
        });

        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('Settings', \Peresmishnyk\BackpackSettings\Facades\Settings::class);
        });

        require __DIR__ . DIRECTORY_SEPARATOR . 'Helpers' . DIRECTORY_SEPARATOR . 'helpers.php';

        $this->addRouteMacro();
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
//        // Publishing the configuration file.
//        if ($this->packageDirectoryExistsAndIsNotEmpty('config')) {
//            $this->publishes([
//                $this->packageConfigFile() => $this->publishedConfigFile(),
//            ], 'config');
//        }
//
//        // Publishing the views.
//        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/views')) {
//            $this->publishes([
//                $this->packageViewsPath() => $this->publishedViewsPath(),
//            ], 'views');
//        }
//
//        // Publishing assets.
//        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/assets')) {
//            $this->publishes([
//                $this->packageAssetsPath() => $this->publishedAssetsPath(),
//            ], 'assets');
//        }
//
//        // Publishing the translation files.
//        if ($this->packageDirectoryExistsAndIsNotEmpty('resources/lang')) {
//            $this->publishes([
//                $this->packageLangsPath() => $this->publishedLangsPath(),
//            ], 'lang');
//        }

        // Registering package commands.
        if (!empty($this->commands)) {
            $this->commands($this->commands);
        }
    }

    /**
     * -------------------
     * CONVENIENCE METHODS
     * -------------------
     */

    protected function vendorNameDotPackageName()
    {
        return $this->vendorName . '.' . $this->packageName;
    }

    protected function vendorNameSlashPackageName()
    {
        return $this->vendorName . '/' . $this->packageName;
    }

    // -------------
    // Package paths
    // -------------

    protected function packageViewsPath()
    {
        return $this->path . '/resources/views';
    }

    protected function packageLangsPath()
    {
        return $this->path . '/resources/lang';
    }

    protected function packageAssetsPath()
    {
        return $this->path . '/resources/assets';
    }

    protected function packageMigrationsPath()
    {
        return $this->path . '/database/migrations';
    }

    protected function packageConfigFile()
    {
        return $this->path . '/config/' . $this->packageName . '.php';
    }

    protected function packageRoutesFile()
    {
        return $this->path . '/routes/' . $this->packageName . '.php';
    }

    protected function packageHelpersFile()
    {
        return $this->path . '/bootstrap/helpers.php';
    }

    // ---------------
    // Published paths
    // ---------------

    protected function publishedViewsPath()
    {
        return base_path('resources/views/vendor/' . $this->vendorName . '/' . $this->packageName);
    }

    protected function publishedConfigFile()
    {
        return config_path($this->vendorNameSlashPackageName() . '.php');
    }

    protected function publishedAssetsPath()
    {
        return public_path('vendor/' . $this->vendorNameSlashPackageName());
    }

    protected function publishedLangsPath()
    {
        return resource_path('lang/vendor/' . $this->vendorName);
    }

    // -------------
    // Miscellaneous
    // -------------

    protected function packageDirectoryExistsAndIsNotEmpty($name)
    {
        // check if directory exists
        if (!is_dir($this->path . '/' . $name)) {
            return false;
        }

        // check if directory has files
        foreach (scandir($this->path . '/' . $name) as $file) {
            if ($file != '.' && $file != '..' && $file != '.gitkeep') {
                return true;
            }
        }

        return false;
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

    private function configOverride()
    {
        $overrides = \Settings::config('config_override');
        foreach ($overrides as $config_key => $settings_key) {
            Config::set($config_key, \Settings::get($settings_key, Config::get($config_key)));
        }
    }
}

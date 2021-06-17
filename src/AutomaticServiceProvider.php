<?php

namespace Peresmishnyk\BackpackSettings;

use Illuminate\Contracts\Foundation\CachesConfiguration;
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

        $this->setupRoutes();
//        $this->configOverride();

    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->packageDirectoryExistsAndIsNotEmpty('config')) {
            $this->mergeConfigFrom($this->packageConfigFile(), 'backpack.settings');
        }

        if (! ($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');
        }

        $this->app->singleton('settings', function ($app) {
            return new Settings('backpack.settings');
        });

        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('Settings', \Peresmishnyk\BackpackSettings\Facades\Settings::class);
        });

        $this->addRouteMacro();
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        if ($this->packageDirectoryExistsAndIsNotEmpty('config')) {
            $this->publishes([
                $this->packageConfigFile() => $this->publishedConfigFile(),
            ], ['config', 'minimum']);
        }

        // Publishing the route file.

        $this->publishes([
            __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'route.php' =>
                base_path('routes' . DIRECTORY_SEPARATOR . 'backpack' . DIRECTORY_SEPARATOR . 'settings.php')
        ], ['minimum', 'custom_routes']);

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

}

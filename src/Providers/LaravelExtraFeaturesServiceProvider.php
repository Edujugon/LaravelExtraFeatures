<?php

namespace Edujugon\AdminLTE\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelExtraFeaturesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishConfigFile();

        $this->addRoutes();
    }


    public function publishConfigFile()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('extrafeatures.php')
        ], 'ExtraFeaturesConfig');
    }

    public function addRoutes()
    {
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../Routes/web.php';
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
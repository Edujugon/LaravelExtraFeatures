<?php

namespace Edujugon\LaravelExtraFeatures\Providers;

use Carbon\Carbon;
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

        if(app('env') != 'local') $this->addRoutes();

        $this->setCarbonDefaultLocale();
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
     *Set Carbon locale based on laravel app locale.
     */
    private function setCarbonDefaultLocale()
    {
        //Set Carbon Localize accordingly to the app Locale.
        if(function_exists('app'))
            Carbon::setLocale(app()->getLocale());
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
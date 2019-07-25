<?php

namespace Baufragen\DataSync;

use Baufragen\DataSync\Helpers\DataSyncContainer;
use Baufragen\DataSync\Helpers\DataSyncHandler;
use Illuminate\Support\ServiceProvider;

class DataSyncServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/datasync.php' => config_path('datasync.php'),
        ], 'datasync');

        $this->loadRoutesFrom(__DIR__ . '/routes/routes.php');
    }

    public function register()
    {
        $this->app->singleton('dataSync.container', function() {
            return new DataSyncContainer();
        });

        $this->app->singleton('dataSync.handler', function() {
            return new DataSyncHandler();
        });
    }

}
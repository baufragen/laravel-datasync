<?php

namespace Baufragen\DataSync;

use Baufragen\DataSync\Commands\ManualDataSyncCommand;
use Baufragen\DataSync\Helpers\DataSyncContainer;
use Baufragen\DataSync\Helpers\DataSyncHandler;
use Illuminate\Support\ServiceProvider;

class DataSyncServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/datasync.php' => config_path('datasync.php'),
        ], 'datasync');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/datasync'),
        ], 'datasync-assets');

        $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'dataSync');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ManualDataSyncCommand::class,
            ]);
        }
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
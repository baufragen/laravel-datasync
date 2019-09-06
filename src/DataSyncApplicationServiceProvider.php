<?php

namespace Baufragen\DataSync;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class DataSyncApplicationServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorization();
    }
    /**
     * Configure the Horizon authorization services.
     *
     * @return void
     */
    protected function authorization()
    {
        $this->gate();

        DataSync::auth(function ($request) {
            return app()->environment('local') ||
                Gate::check('viewDataSyncDashboard', [$request->user()]);
        });
    }
    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewDataSyncDashboard', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
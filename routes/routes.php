<?php

Route::prefix('datasync')->group(function() {

    Route::post('handle_incoming', [\Baufragen\DataSync\Controllers\DataSyncController::class, 'handleIncomingSync'])->name('dataSync.handle');

    Route::prefix('dashboard')->group(function () {

        Route::get('/', [\Baufragen\DataSync\Controllers\DashboardController::class, 'view'])
            ->name('dataSync.dashboard.view');

        Route::get('/{log}', [\Baufragen\DataSync\Controllers\DashboardController::class, 'details'])
            ->middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class)
            ->name('dataSync.dashboard.details');

    });

});

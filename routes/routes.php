<?php

Route::prefix('data_sync')->group(function() {

    Route::post('handle_incoming', [\Baufragen\DataSync\Controllers\DataSyncController::class, 'handleIncomingSync'])->name('dataSync.handle');

    Route::prefix('dashboard')->group(function () {

        Route::get('/', [\Baufragen\DataSync\Controllers\DataSyncController::class, 'view'])->name('dataSync.dashboard.view');

    });

});

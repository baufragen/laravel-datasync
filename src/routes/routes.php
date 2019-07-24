<?php

Route::prefix('data_sync')->group(function() {

    Route::post('handle_incoming', [\Baufragen\DataSync\Controllers\DataSyncController::class, 'handleIncomingSync'])->name('dataSync.handle');

});

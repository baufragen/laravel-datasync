<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Jobs\HandleDataSync;

class DataSyncHandler {
    public function executeSync($action, $model) {
        if (app()->environment('testing')) {
            return;
        }

        $syncName       = $model->getSyncName();
        $data           = $model->customizeSyncableData();
        $identifier     = $model->id;
        $shouldLog      = $model->dataSyncShouldBeLogged();
        $relationdata   = $model->getSyncedRelationData();

        $connections = collect($model->dataSyncConnections());

        $connections->each(function($connection) use ($syncName, $data, $identifier, $action, $relationdata, $shouldLog) {
            if (config('datasync.connections.' . $connection . '.enabled')) {
                HandleDataSync::dispatch($connection, $syncName, $data, $action, $relationdata, $identifier, $shouldLog);
            }
        });
    }
}

<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Jobs\HandleDataSync;

class DataSyncHandler {
    public function executeSync($action, $model) {
        $syncName   = $model->getSyncName();
        $data       = $model->customizeSyncableData();
        $identifier = $model->id;

        $connections = collect($model->dataSyncConnections());

        $connections->each(function($connection) use ($syncName, $data, $identifier, $action) {
            if (config('datasync.connections.' . $connection . '.enabled')) {
                HandleDataSync::dispatch($connection, $syncName, $data, $action, $identifier);
            }
        });
    }
}

<?php

if (!function_exists('dataSync')) {
    /**
     * @param \Baufragen\DataSync\Interfaces\DataSyncing $model
     * @param \Baufragen\DataSync\Helpers\DataSyncAction $action
     * @return \Baufragen\DataSync\Helpers\DataSyncCollector
     */
    function dataSync(\Baufragen\DataSync\Interfaces\DataSyncing $model, \Baufragen\DataSync\Helpers\DataSyncAction $action) {
        return app('dataSync.handler')->getCollectorForModel($model, $action);
    }
}

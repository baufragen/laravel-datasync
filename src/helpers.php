<?php

if (!function_exists('dataSync')) {
    function dataSync(\Baufragen\DataSync\Interfaces\DataSyncing $model, \Baufragen\DataSync\Helpers\DataSyncAction $action) {
        return app('dataSync.handler')->getCollectorForModel($model, $action);
    }
}

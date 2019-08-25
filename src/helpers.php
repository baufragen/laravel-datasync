<?php

if (!function_exists('dataSync')) {
    function dataSync(\Baufragen\DataSync\Interfaces\DataSyncing $model, string $action) {
        return app('dataSync.handler')->getCollectorForModel($model, $action);
    }
}

<?php

if (!function_exists('dataSync')) {
    /**
     * @param Baufragen\DataSync\Interfaces\DataSyncing $model
     * @param string $collectorClass
     * @return Baufragen\DataSync\Collectors\DataSyncCollecting
     */
    function dataSync(\Baufragen\DataSync\Interfaces\DataSyncing $model, string $collectorClass, ...$afterCreationParameters) {
        $collector = app('dataSync.handler')->getCollectorForModel($model, $collectorClass);

        if (method_exists($collector, "afterCreation")) {
            $collector->afterCreation(...$afterCreationParameters);
        }

        return $collector;
    }
}

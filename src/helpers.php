<?php

if (!function_exists('dataSync')) {
    /**
     * @param Baufragen\DataSync\Interfaces\DataSyncing $model
     * @param string $collectorClass
     * @return Baufragen\DataSync\Interfaces\DataSyncCollecting
     */
    function dataSync(\Baufragen\DataSync\Interfaces\DataSyncing $model, string $collectorClass, ...$afterCreationParameters) {
        if (!\Baufragen\DataSync\DataSync::isEnabled() || !$model->dataSyncEnabled()) {
            return optional(null);
        }

        $collector = app('dataSync.handler')->getCollectorForModel($model, $collectorClass);

        if (method_exists($collector, "afterCreation")) {
            $collector->afterCreation(...$afterCreationParameters);
        }

        return $collector;
    }
}

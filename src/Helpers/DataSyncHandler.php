<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Interfaces\DataSyncing;
use Baufragen\DataSync\Jobs\HandleDataSync;

class DataSyncHandler {
    protected $dataCollectors;

    public function __construct() {
        $this->dataCollectors = collect([]);
    }

    public function getCollectorForModel(DataSyncing $model, DataSyncAction $action) {
        if (!empty($this->dataCollectors[$model->getSyncName()])) {
            if ($collector = $this->dataCollectors[$model->getSyncName()]->filter(function ($collectionModel) use ($model) {
                return $collectionModel->is($model);
            })->first()) {
                return $collector;
            }
        }
        return $this->createDataCollectorForModel($model, $action);
    }

    public function hasOpenSyncs() {
        return $this->dataCollectors->isNotEmpty();
    }

    public function dispatch() {
        if (app()->environment('testing')) {
            return;
        }

        $this->dataCollectors->each(function ($collectors) {
            $collectors->each(function ($collector) {
                HandleDataSync::dispatch($collector);
            });
        });
    }

    protected function createDataCollectorForModel(DataSyncing $model, DataSyncAction $action) {
        if (!isset($this->dataCollectors[$model->getSyncName()])) {
            $this->dataCollectors[$model->getSyncName()] = collect([]);
        }

        $collector = new DataSyncCollector($action);
        $collector->initForModel($model);

        $this->dataCollectors[$model->getSyncName()]->push($collector);

        return $collector;
    }
}

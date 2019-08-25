<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Interfaces\DataSyncing;
use Baufragen\DataSync\Jobs\HandleDataSync;

class DataSyncHandler {
    protected $dataCollectors;

    public function __construct() {
        $this->dataCollectors = collect([]);
    }

    public function getCollectorForModel(DataSyncing $model, $action = DataSyncAction::UPDATE) {
        return $this->dataCollectors[$model->getSyncName()]->filter(function ($collectionModel) use ($model) {
                return $collectionModel->is($model);
            })->first() ?? $this->createDataCollectorForModel($model, $action);
    }

    public function hasOpenSyncs() {
        return $this->dataCollectors->isNotEmpty();
    }

    public function dispatch() {
        $this->dataCollectors->each(function ($collectors) {
            $collectors->each(function ($collector) {
                HandleDataSync::dispatch($collector);
            });
        });
    }

    protected function createDataCollectorForModel(DataSyncing $model, $action) {
        if (!isset($this->dataCollectors[$model->getSyncName()])) {
            $this->dataCollectors[$model->getSyncName()] = collect([]);
        }

        $collector = new DataSyncCollector($action);
        $collector->initForModel($model);

        $this->dataCollectors[$model->getSyncName()]->push($collector);

        return $collector;
    }
}

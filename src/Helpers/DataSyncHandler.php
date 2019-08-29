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
        if (app()->environment('testing')) {
            return $this->createDataCollectorForModel($model, new DataSyncAction(DataSyncAction::DUMMY));
        }

        if (!empty($this->dataCollectors[$model->getSyncName()])) {
            if ($collector = $this->dataCollectors[$model->getSyncName()]->filter(function ($collector) use ($model) {
                return $collector->getModel()->is($model) ?? false;
            })->first()) {
                return $collector;
            }
        }
        return $this->createDataCollectorForModel($model, $action);
    }

    public function hasOpenSyncs() {
        return $this->dataCollectors
            ->map(function (DataSyncCollector $collector) {
                return !$collector->isDummy();
            })
            ->isNotEmpty();
    }

    public function dispatch() {
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

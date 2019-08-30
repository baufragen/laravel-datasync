<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Collectors\DataSyncCollecting;
use Baufragen\DataSync\Collectors\DummyCollector;
use Baufragen\DataSync\Exceptions\CollectorClassNotFoundException;
use Baufragen\DataSync\Exceptions\TransformerClassNotFoundException;
use Baufragen\DataSync\Interfaces\DataSyncing;
use Baufragen\DataSync\Jobs\HandleDataSync;
use Illuminate\Http\Request;

class DataSyncHandler {
    protected $dataCollectors;

    public function __construct() {
        $this->dataCollectors = collect([]);
    }

    public function getTransformerForType($type, Request $request) {
        $transformerClass = config('datasync.transformers.' . $type);

        if (!$transformerClass) {
            throw new TransformerClassNotFoundException("No Transformer found [" . $type . "]");
        }

        $connection = new DataSyncConnection($request->get('connection'));

        return new $transformerClass($request, $connection);
    }

    public function getCollectorForModel(DataSyncing $model, string $collectorClass) {
        if (app()->environment('testing')) {
            return $this->createDataCollectorForModel($model, DummyCollector::class);
        }

        return $this->createDataCollectorForModel($model, $collectorClass);
    }

    public function hasOpenSyncs() {
        return $this
            ->nonDummyCollectors()
            ->isNotEmpty();
    }

    public function dispatch() {
        $this
            ->nonDummyCollectors()
            ->each(function (DataSyncCollecting $collector) {
                if (!$collector->getModel()->beforeDataSync($collector)) {
                    return;
                }
                HandleDataSync::dispatch($collector);
            });
    }

    protected function createDataCollectorForModel(DataSyncing $model, string $collectorClass) {
        if (!class_exists($collectorClass)) {
            $configCollectorClass = config('datasync.collectors.' . $collectorClass);

            if (!$configCollectorClass || !class_exists($configCollectorClass)) {
                throw new CollectorClassNotFoundException("Collector class not found [" . $collectorClass . "]");
            }

            $collectorClass = $configCollectorClass;
        }
        $collector = new $collectorClass($model);

        $this->dataCollectors->push($collector);

        return $collector;
    }

    protected function nonDummyCollectors() {
        return $this->dataCollectors
            ->filter(function (DataSyncCollecting $collector) {
                return $collector->getType() !== 'dummy';
            });
    }
}

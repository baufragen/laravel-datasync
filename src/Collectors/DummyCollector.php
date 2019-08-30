<?php

namespace Baufragen\DataSync\Collectors;

use Baufragen\DataSync\Interfaces\DataSyncCollecting;
use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncing;

class DummyCollector extends BaseCollector implements DataSyncCollecting {
    public function __construct(DataSyncing $model) {

    }

    public function __call($name, $arguments) {
        return null;
    }

    public function transform(DataSyncConnection $connection) {
        return $this;
    }

    public function getType() {
        return 'dummy';
    }
}

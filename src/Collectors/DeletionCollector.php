<?php

namespace Baufragen\DataSync\Collectors;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncCollecting;
use Baufragen\DataSync\Interfaces\DataSyncing;

class DeletionCollector extends BaseCollector implements DataSyncCollecting {

    public function __construct(DataSyncing $model) {
        parent::__construct();

        $this->setModel($model);
        $this->identifier($model->id);
    }

    public function transform(DataSyncConnection $connection) {
        $result = [
        ];

        return $result;
    }

    public function getType() {
        return 'deletion';
    }
}

<?php

namespace Baufragen\DataSync\Collectors;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncing;

class RawCollector extends BaseCollector implements DataSyncCollecting {
    public function __construct(DataSyncing $model) {
        parent::__construct();

        $this->setModel($model);
        $this->identifier($model->id);
    }

    public function transform(DataSyncConnection $connection) {
        $rawData = $this->model->getRawSyncData();

        if (!$rawData) {
            return null;
        }

        $rawData = json_encode($rawData);

        return [
            'name' => 'rawdata',
            'contents' => $connection->isEncrypted() ? encrypt($rawData) : $rawData,
        ];
    }

    public function getType() {
        return 'raw';
    }
}

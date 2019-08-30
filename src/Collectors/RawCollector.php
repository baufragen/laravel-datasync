<?php

namespace Baufragen\DataSync\Collectors;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncCollecting;
use Baufragen\DataSync\Interfaces\DataSyncing;

class RawCollector extends BaseCollector implements DataSyncCollecting {
    protected $rawData = [];

    public function __construct(DataSyncing $model) {
        parent::__construct();

        $this->setModel($model);
        $this->identifier($model->id);
    }

    public function add($key, $value) {
        $this->rawData[$key] = $value;
    }

    public function transform(DataSyncConnection $connection) {
        if (empty($this->rawData)) {
            return null;
        }

        $rawData = json_encode($this->rawData);

        return [
            [
            'name' => 'rawdata',
            'contents' => $connection->isEncrypted() ? encrypt($rawData) : $rawData,
            ],
        ];
    }

    public function getType() {
        return 'raw';
    }
}

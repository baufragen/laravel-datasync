<?php

namespace Baufragen\DataSync\Collectors;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncCollecting;
use Baufragen\DataSync\Interfaces\DataSyncing;

class ActionCollector extends BaseCollector implements DataSyncCollecting {

    protected $action;
    protected $additionalData;

    public function __construct(DataSyncing $model) {
        parent::__construct();

        $this->setModel($model);
        $this->identifier($model->id);
    }

    public function action($name, $additionalData = null) {
        $this->action = $name;
        $this->additionalData = $additionalData;
    }

    public function transform(DataSyncConnection $connection) {
        if (empty($this->action)) {
            return null;
        }

        $result = [
            [
                'name'      => 'executableaction',
                'contents'  => $this->action,
            ],
        ];

        if (!empty($this->additionalData)) {
            $encoded = json_encode($this->additionalData);

            $result[] = [
                'name'      => 'data',
                'contents'  => $connection->isEncrypted() ? encrypt($encoded) : $encoded,
            ];
        }

        return $result;
    }

    public function getType() {
        return 'action';
    }
}

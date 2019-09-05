<?php

namespace Baufragen\DataSync\Collectors;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncCollecting;
use Baufragen\DataSync\Interfaces\DataSyncing;

class RelationCollector extends BaseCollector implements DataSyncCollecting {

    protected $type;
    protected $relation;
    protected $id;
    protected $pivotData = [];

    public function __construct(DataSyncing $model) {
        parent::__construct();

        $this->setModel($model);
        $this->identifier($model->id);
    }

    public function attach($relation, $id, $pivotData = []) {
        $this->setBaseValues($relation, $id, $pivotData);
        $this->type = "attach";
    }

    public function detach($relation, $id, $pivotData = []) {
        $this->setBaseValues($relation, $id, $pivotData);
        $this->type = "detach";
    }

    public function update($relation, $id, $pivotData = []) {
        $this->setBaseValues($relation, $id, $pivotData);
        $this->type = "update";
    }

    protected function setBaseValues($relation, $id, $pivotData) {
        $this->relation = $relation;
        $this->id       = $id;
        $this->pivotData= $pivotData;
    }

    public function transform(DataSyncConnection $connection) {
        if (empty($this->relation) || empty($this->id) || empty($this->type)) {
            return null;
        }

        $pivotData = json_encode($this->pivotData);

        return [
            [
                'name'      => 'relation[name]',
                'contents'  => $this->relation,
            ],
            [
                'name'      => 'relation[type]',
                'contents'  => $this->type,
            ],
            [
                'name'      => 'relation[id]',
                'contents'  => $this->id,
            ],
            [
                'name'      => 'relation[pivot]',
                'contents'  => $connection->isEncrypted() ? encrypt($pivotData) : $pivotData,
            ],
        ];
    }

    public function getType() {
        return 'relation';
    }
}

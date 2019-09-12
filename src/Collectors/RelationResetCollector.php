<?php

namespace Baufragen\DataSync\Collectors;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncCollecting;
use Baufragen\DataSync\Interfaces\DataSyncing;

class RelationResetCollector extends BaseCollector implements DataSyncCollecting {

    protected $type;
    protected $relation;
    protected $relationData;

    public function __construct(DataSyncing $model) {
        parent::__construct();

        $this->relationData = collect([]);

        $this->setModel($model);
        $this->identifier($model->id);
    }

    public function attach($id, $pivotData = []) {
        $this->relationData->push([
            'id'    => $id,
            'pivot' => $pivotData,
        ]);
    }

    public function transform(DataSyncConnection $connection) {
        if (empty($this->relation)) {
            return null;
        }

        $result = [
            [
                'name'      => 'relation',
                'contents'  => $this->relation,
            ]
        ];

        $this->relationData->each(function ($data, $index) use ($connection, &$result) {
            $pivotData = null;
            if (!empty($data['pivot'])) {
                $pivotData = json_encode($data['pivot']);
            }

            $result[] = [
                'name'      => 'relationdata[' . $index . '][id]',
                'contents'  => $data['id'],
            ];

            $result[] = [
                'name'      => 'relationdata[' . $index . '][pivot]',
                'contents'  => $connection->isEncrypted() ? encrypt($pivotData) : $pivotData,
            ];
        });

        return $result;
    }

    public function getType() {
        return 'relationreset';
    }

    public function afterCreation($relation) {
        $this->relation = $relation;
    }
}

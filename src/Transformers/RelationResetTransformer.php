<?php

namespace Baufragen\DataSync\Transformers;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncing;
use Illuminate\Http\Request;

class RelationResetTransformer extends BaseTransformer {
    protected $relation;
    protected $relationData;

    public function __construct(Request $request, DataSyncConnection $connection)
    {
        parent::__construct($request, $connection);

        $this->relationData = collect([]);

        $this->relation     = $this->getRelationFromRequest($request);
        $this->relationData = $this->getRelationDataFromRequest($request);
    }

    public function sync() {
        if (method_exists($this->model, "beforeDataSyncExecution")) {
            if (!$this->model->beforeDataSyncExecution($this)) {
                return;
            }
        }

        $this->model->executeRelationSyncReset($this->relation, $this->relationData->toArray());

        if (method_exists($this->model, "afterDataSyncExecution")) {
            if (!$this->model->afterDataSyncExecution($this)) {
                return;
            }
        }
    }

    protected function getRelationFromRequest(Request $request) {
        return $request->input('relation');
    }

    protected function getRelationDataFromRequest(Request $request) {
        return collect($request->input('relationdata'))
            ->mapWithKeys(function ($data) {
                $pivotData = $data['pivot'];

                if ($this->connection->isEncrypted()) {
                    $pivotData = decrypt($data['pivot']);
                }

                $pivotData = json_decode($pivotData, true);

                return [$data['id'] => !empty($pivotData) ? $pivotData : []];
            });
    }

    protected function validationRules() {
        return [
            'relation'              => 'required',
            'relationdata'          => 'nullable|array',
            'relationdata.*.id'     => 'integer',
        ];
    }

    public function getType() {
        return 'relationreset';
    }
}

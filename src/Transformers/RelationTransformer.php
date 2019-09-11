<?php

namespace Baufragen\DataSync\Transformers;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncing;
use Illuminate\Http\Request;

class RelationTransformer extends BaseTransformer {
    protected $relation;
    protected $type;
    protected $id;
    protected $pivotData;

    public function __construct(Request $request, DataSyncConnection $connection)
    {
        parent::__construct($request, $connection);

        $this->relation     = $this->getRelationFromRequest($request);
        $this->type         = $this->getTypeFromRequest($request);
        $this->id           = $this->getIdFromRequest($request);
        $this->pivotData    = $this->getPivotDataFromRequest($request);
    }

    public function sync() {
        if (method_exists($this->model, "beforeDataSyncExecution")) {
            if (!$this->model->beforeDataSyncExecution($this)) {
                return;
            }
        }

        switch ($this->type) {
            case "attach":
                $this->model->executeRelationSyncAttach($this->relation, $this->id, $this->pivotData);
                break;

            case "detach":
                $this->model->executeRelationSyncDetach($this->relation, $this->id, $this->pivotData);
                break;

            case "update":
                $this->model->executeRelationSyncUpdate($this->relation, $this->id, $this->pivotData);
                break;
        }

        if (method_exists($this->model, "afterDataSyncExecution")) {
            if (!$this->model->afterDataSyncExecution($this)) {
                return;
            }
        }
    }

    protected function getRelationFromRequest(Request $request) {
        return $request->input('relation.name');
    }

    protected function getIdFromRequest(Request $request) {
        return $request->input('relation.id');
    }

    protected function getTypeFromRequest(Request $request) {
        return $request->input('relation.type');
    }

    protected function getPivotDataFromRequest(Request $request) {
        $pivot = $request->input('relation.pivot');
        if (!empty($pivot)) {
            if ($this->connection->isEncrypted()) {
                $pivot = decrypt($pivot);
            }

            $pivot = json_decode($pivot, true);
        }

        return $pivot ?? [];
    }

    protected function validationRules() {
        return [
            'relation.name' => 'required',
            'relation.type' => 'required',
            'relation.id'   => 'required',
        ];
    }

    public function getType() {
        return 'relation';
    }
}

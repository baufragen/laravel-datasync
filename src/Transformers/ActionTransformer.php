<?php

namespace Baufragen\DataSync\Transformers;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncing;
use Illuminate\Http\Request;

class ActionTransformer extends BaseTransformer {
    protected $action;
    protected $additionalData = null;

    public function __construct(Request $request, DataSyncConnection $connection)
    {
        parent::__construct($request, $connection);

        $this->action = $this->getActionFromRequest($request);
        $this->additionalData = $this->getAdditionalDataFromRequest($request);
    }

    public function sync() {
        if (method_exists($this->model, "beforeDataSyncExecution")) {
            if (!$this->model->beforeDataSyncExecution($this)) {
                return;
            }
        }

        $this->model->executeActionDataSync($this->action, $this->additionalData);

        if (method_exists($this->model, "afterDataSyncExecution")) {
            if (!$this->model->afterDataSyncExecution($this)) {
                return;
            }
        }
    }

    protected function getActionFromRequest(Request $request) {
        return $request->get('executableaction');
    }

    protected function getAdditionalDataFromRequest(Request $request) {
        if ($request->filled('data')) {
            return json_decode($this->connection->isEncrypted() ? decrypt($request->get('data')) : $request->get('data'), true);
        }

        return null;
    }

    protected function validationRules() {
        return [
            'executableaction' => 'required',
        ];
    }

    protected function getModelFromRequest(Request $request) {
        $modelClass = app('dataSync.container')->getClassBySyncName($request->get('model'));

        if ($request->filled('identifier')) {
            $model = $modelClass::find($request->get('identifier'));
        }

        if (empty($model)) {
            $model = new $modelClass();
        }

        if (!$model instanceof DataSyncing) {
            throw new \Exception("Class " . $modelClass . " does not implement DataSyncing interface");
        }

        return $model;
    }
}

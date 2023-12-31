<?php

namespace Baufragen\DataSync\Transformers;

use Baufragen\DataSync\Interfaces\DataSyncTransforming;
use Baufragen\DataSync\Helpers\DataSyncAction;
use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseTransformer implements DataSyncTransforming {
    protected $request;
    protected $connection;
    protected $model;
    protected $hooks;

    public function __construct(Request $request, DataSyncConnection $connection) {
        $this->request = $request;
        $this->connection = $connection;

        $this->model = $this->getModelFromRequest($request);
    }

    public function validate() {
        if (method_exists($this, 'validationRules')) {
            $validator = Validator::make($this->request->all(), $this->validationRules());

            if ($validator->fails()) {
                throw ValidationException::withMessages($validator->errors()->toArray());
            }
        }
    }

    protected function getModelFromRequest(Request $request) {
        $modelClass = app('dataSync.container')->getClassBySyncName($request->get('model'));

        if ($request->filled('action')) {
            $action = new DataSyncAction($request->get('action'));

            if ($action->isUpdate() || $action->isDelete()) {
                if ($action->isUpdateOrCreate()) {
                    $model = $modelClass::find($request->get('identifier'));

                    if (!$model) {
                        $model = new $modelClass();
                    }
                } else {
                    $model = $modelClass::findOrFail($request->get('identifier'));
                }
            } else {
                $model = new $modelClass();
            }
        } else {
            $model = $modelClass::findOrFail($request->get('identifier'));
        }

        if (!$model instanceof DataSyncing) {
            throw new \Exception("Class " . $modelClass . " does not implement DataSyncing interface");
        }

        return $model;
    }

    public function addHook(string $type, callable $callback) {
        if (empty($this->hooks[$type])) {
            $this->hooks[$type] = [];
        }

        $this->hooks[$type][] = $callback;
    }

    protected function executeHooks(string $type) {
        if (empty($this->hooks[$type])) {
            return;
        }

        foreach ($this->hooks[$type] as $hook) {
            $hook($this);
        }
    }

    public function getModel() {
        return $this->model;
    }
}

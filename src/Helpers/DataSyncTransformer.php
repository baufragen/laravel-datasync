<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Traits\HasDataSync2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DataSyncTransformer {
    protected $request;
    protected $model;

    protected $action;
    protected $attributes;
    protected $files;
    protected $relations;

    public function __construct(Request $request) {
        $this->request  = $request;

        $this->transformData($request);
    }

    public function executeDataSync() {
        $this->model->executeDataSync($this);
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function getRelations() {
        return $this->relations;
    }

    public function getFiles() {
        return $this->files;
    }

    protected function transformData(Request $request) {
        $this->action       = $this->getActionFromRequest($request);
        $this->model        = $this->getModelFromRequest($request);
        $this->attributes   = $this->getAttributesFromRequest($request);
        $this->files        = $this->getFilesFromRequest($request);
        $this->relations    = $this->getRelationsFromRequest($request);

        $this->validate($this->attributes, $this->model);
    }

    protected function validate($data, $model) {
        if (method_exists($model, "validateDataSync")) {
            if (!$model->validateDataSync($data)) {
                // TODO: handle failed validation (exception)
            }
        } else if (method_exists($model, "dataSyncValidationRules")) {
            Validator::make($data, $model->dataSyncValidationRules())
                ->validate();
        }
    }

    protected function getAttributesFromRequest(Request $request) {
        return collect($request->get('data', []))
            ->when($request->get('encrypted', false), function ($data) {
                return $data->mapWithKeys(function ($value, $key) {
                    return [$key => decrypt($value)];
                });
            })
            ->toArray();
    }

    protected function getFilesFromRequest(Request $request) {
        if (!$request->hasFile('files')) {
            return null;
        }

        return collect($request->get('files'))
            ->mapWithKeys(function ($file, $key) {
                return [$key => $file];
            })
            ->toArray();
    }

    protected function getRelationsFromRequest(Request $request) {
        if (!$request->filled('relationdata')) {
            return null;
        }

        return $request->get('relationdata');
    }

    protected function getActionFromRequest(Request $request) {
        return new DataSyncAction($request->get('action', DataSyncAction::CREATE));
    }

    protected function getModelFromRequest(Request $request) {
        $modelClass = app('dataSync.container')->getClassBySyncName($request->get('model'));

        if ($this->action->isUpdate() || $this->action->isDelete()) {
            $model = $modelClass::findOrFail($request->get('identifier'));
        } else {
            $model = new $modelClass();
        }

        if (!method_exists($model, "getSyncedAttributeData")) {
            abort(500, "Class " . $modelClass . " does not implement HasDataSync trait");
        }

        return $model;
    }
}

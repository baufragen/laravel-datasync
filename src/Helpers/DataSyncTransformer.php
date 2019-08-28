<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Interfaces\DataSyncing;
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
    protected $customActions;

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

    public function getCustomActions() {
        return $this->customActions;
    }

    protected function transformData(Request $request) {
        $this->action       = $this->getActionFromRequest($request);
        $this->model        = $this->getModelFromRequest($request);
        $this->attributes   = $this->getAttributesFromRequest($request);
        $this->files        = $this->getFilesFromRequest($request);
        $this->relations    = $this->getRelationsFromRequest($request);
        $this->customActions = $this->getCustomActionsFromRequest($request);

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
            ->map(function ($value) {
                if (strpos($value, "bool:") === 0) {
                    return (bool)substr($value, 5);
                }

                return $value;
            })
            ->toArray();
    }

    protected function getFilesFromRequest(Request $request) {
        if (!$request->hasFile('files')) {
            return null;
        }

        return collect($request->file('files'))
            ->mapWithKeys(function ($file, $key) {
                return [$key => $file];
            })
            ->toArray();
    }

    protected function getRelationsFromRequest(Request $request) {
        if (!$request->filled('relationdata')) {
            return null;
        }

        return collect($request->get('relationdata'))
            ->when($request->get('encrypted', false), function ($relations) {
                return $relations
                    ->map(function ($relation) {
                        return decrypt($relation);
                    });
            })
            ->mapWithKeys(function ($data, $relation) {
                return [$relation => json_decode($data, true)];
            })
            ->toArray();
    }

    protected function getCustomActionsFromRequest(Request $request) {
        if (!$request->filled('customactions')) {
            return null;
        }

        return collect($request->get('customactions', []))
            ->when($request->get('encrypted', false), function ($customActions) {
                return $customActions->mapWithKeys(function ($datasets, $action) {
                    return [
                        $action => collect($datasets)->map(function($dataset) {
                            return json_decode(decrypt($dataset), true);
                        })
                        ->toArray()
                    ];
                });
            })
            ->toArray();
    }

    protected function getActionFromRequest(Request $request) {
        return new DataSyncAction($request->get('action', DataSyncAction::CREATE));
    }

    protected function getModelFromRequest(Request $request) {
        $modelClass = app('dataSync.container')->getClassBySyncName($request->get('model'));

        if ($this->action->isUpdate() || $this->action->isDelete()) {
            if ($this->action->isUpdateOrCreate()) {
                $model = $modelClass::find($request->get('identifier'));

                if (!$model && $this->action->isUpdateOrCreate()) {
                    $model = new $modelClass();
                }
            } else {
                $model = $modelClass::findOrFail($request->get('identifier'));
            }
        } else {
            $model = new $modelClass();
        }

        if (!$model instanceof DataSyncing) {
            abort(500, "Class " . $modelClass . " does not implement DataSyncing interface");
        }

        return $model;
    }
}

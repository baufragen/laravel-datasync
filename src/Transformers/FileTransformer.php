<?php

namespace Baufragen\DataSync\Transformers;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Illuminate\Http\Request;

class FileTransformer extends BaseTransformer {
    protected $files;

    public function __construct(Request $request, DataSyncConnection $connection)
    {
        parent::__construct($request, $connection);

        $this->files = $this->getFilesFromRequest($request);
    }

    protected function validationRules() {
        return [
            'files'     => 'required|array',
            'files.*'   => 'file',
        ];
    }

    public function sync() {
        if (method_exists($this->model, "beforeDataSyncExecution")) {
            if (!$this->model->beforeDataSyncExecution($this)) {
                return;
            }
        }

        $this->files->each(function ($file, $name) {
            $this->model->executeFileDataSync($name, $file);
        });

        if (method_exists($this->model, "afterDataSyncExecution")) {
            if (!$this->model->afterDataSyncExecution($this)) {
                return;
            }
        }
    }

    protected function getFilesFromRequest(Request $request) {
        if (!$request->hasFile('files')) {
            return collect([]);
        }

        return collect($request->file('files'))
            ->mapWithKeys(function ($file, $key) {
                return [$key => $file];
            });
    }
}

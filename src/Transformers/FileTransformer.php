<?php

namespace Baufragen\DataSync\Transformers;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Illuminate\Http\Request;

class FileTransformer extends BaseTransformer {
    protected $files;
    protected $deletedFiles;

    public function __construct(Request $request, DataSyncConnection $connection)
    {
        parent::__construct($request, $connection);

        $this->files        = $this->getFilesFromRequest($request);
        $this->deletedFiles = $this->getDeletedFilesFromRequest($request);
    }

    protected function validationRules() {
        return [
            'files'         => 'required_without:deletedfiles|array',
            'files.*'       => 'file',
            'deletedfiles'  => 'required_without:files',
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

        $this->deletedFiles->each(function ($name) {
            $this->model->executeFileDataSyncDeletion($name);
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

    protected function getDeletedFilesFromRequest(Request $request) {
        return collect($request->input('deletedfiles'));
    }

    public function getType() {
        return 'file';
    }
}

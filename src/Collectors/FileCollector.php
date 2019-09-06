<?php

namespace Baufragen\DataSync\Collectors;

use Baufragen\DataSync\Interfaces\DataSyncCollecting;
use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncing;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class FileCollector extends BaseCollector implements DataSyncCollecting {
    protected $files;
    protected $deletedFiles;

    public function __construct(DataSyncing $model) {
        parent::__construct();

        $this->files        = collect([]);
        $this->deletedFiles = collect([]);

        $this->setModel($model);
        $this->identifier($model->id);
    }

    public function file($name, $path, $fileName = null, $mimeType = null) {
        if (!file_exists($path)) {
            throw new \Exception("File " . $path . " doesn't exist");
        }

        if (empty($fileName)) {
            $fileName = pathinfo($path, PATHINFO_FILENAME);
        }

        if (empty($mimeType)) {
            $mimeType = app(MimeTypeGuesser::class)->guess($path);
        }

        $this->files[$name] = [
            'path' => $path,
            'fileName' => $fileName,
            'mimeType' => $mimeType,
        ];

        return $this;
    }

    public function deleteFile($name) {
        $this->deletedFiles->push($name);

        return $this;
    }

    public function transform(DataSyncConnection $connection) {
        $this->model->beforeDataSyncFiles($this);

        $result = [];

        if ($this->files->isNotEmpty()) {
            $result = array_merge(
                $result,
                $this->files
                    ->map(function ($file, $name) {
                        if (!file_exists($file['path'])) {
                            return null;
                        }

                        return array_merge(
                            [
                                'name'      => 'files[' . $name . ']',
                                'contents'  => fopen($file['path'], 'r'),
                            ],
                            !empty($file['fileName']) ? ['filename' => $file['fileName']] : [],
                            !empty($file['mimeType']) ? ['headers' => ['Content-Type' => $file['mimeType']]] : []
                        );
                    })
                    ->filter(function ($file) {
                        return !empty($file);
                    })
                    ->toArray()
            );
        }

        if ($this->deletedFiles->isNotEmpty()) {
            $result = array_merge(
                $result,
                $this->deletedFiles
                    ->map(function ($name) {
                        return [
                            'name'      => 'deletedfiles[]',
                            'contents'  => $name,
                        ];
                    })
                    ->toArray()
            );
        }

        return $result;
    }

    public function getType() {
        return 'file';
    }
}

<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Interfaces\DataSyncing;
use Baufragen\DataSync\Traits\HasDataSync;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class DataSyncCollector {
    protected $action;
    protected $files;
    protected $relatedData;
    protected $customActions;
    protected $attributes;
    protected $connections;

    protected $identifier       = null;
    protected $syncName         = null;
    protected $loggingEnabled   = true;

    protected $model            = null;

    public function __construct(DataSyncAction $action) {
        $this->action = $action;
        $this->files        = collect([]);
        $this->relatedData  = collect([]);
        $this->attributes   = collect([]);
        $this->customActions= collect([]);
        $this->connections  = collect([]);
    }

    /**
     * Takes a model with the HasDataSync trait and initializes
     * the basic attributes in the collector.
     *
     * @param HasDataSync $model
     * @return DataSyncCollector $this
     */
    public function initForModel(DataSyncing $model) {
        $this->model = $model;

        if (app()->environment('testing')) {
            return $this;
        }

        $this->syncName         = $model->getSyncName();
        $this->identifier($model->id);
        $this->attributes       = $model->getDirtySyncedAttributeData();
        $this->loggingEnabled   = $model->dataSyncShouldBeLogged();

        if (method_exists($model, 'getSyncedConnections')) {
            $this->connections  = collect($model->getSyncedConnections());
        } else {
            $this->connections  = collect(config('datasync.connections'))->keys();
        }

        $this->customActions    = $model->getCustomDataSyncActions();

        return $this;
    }

    public function completeSync() {
        $this->attributes       = $this->model->getAllSyncedAttributeData();
    }

    /**
     * Allows additional attributes to be synced
     *
     * @param string $name
     * @param mixed $value
     * @return DataSyncCollector $this
     */
    public function addAttribute($name, $value) {
        if (app()->environment('testing')) {
            return $this;
        }

        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Adds a file to be synced.
     *
     * @param string $name
     * @param string $path
     * @param null|string $fileName
     * @param null|string $mimeType
     * @return DataSyncCollector $this
     * @throws \Exception
     */
    public function file($name, $path, $fileName = null, $mimeType = null) {
        if (app()->environment('testing')) {
            return $this;
        }

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

    /**
     * Allows the user to add a custom action (for example the deletion of a file)
     * to the sync process.
     *
     * @param string $action
     * @param mixed $data
     * @return DataSyncCollector $this
     */
    public function customAction($action, $data) {
        if (app()->environment('testing')) {
            return $this;
        }

        if (!isset($this->customActions[$action])) {
            $this->customActions[$action] = collect([]);
        }

        $this->customActions[$action]->push($data);

        return $this;
    }

    /**
     * Adds a new related item that got added with this save.
     *
     * @param string $relation
     * @param integer $id
     * @param array $pivotData
     * @return DataSyncCollector $this
     */
    public function addRelation($relation, $id, $pivotData = []) {
        if (app()->environment('testing')) {
            return $this;
        }

        $this->checkRelationExists($relation);

        $this->relatedData[$relation]['add']->push([
            'id'    => $id,
            'pivot' => $pivotData,
        ]);

        return $this;
    }

    /**
     * Update pivot data on given relation.
     *
     * @param string $relation
     * @param integer $id
     * @param array $pivotData
     * @return DataSyncCollector$this
     */
    public function updateRelation($relation, $id, $pivotData = []) {
        if (app()->environment('testing')) {
            return $this;
        }

        $this->checkRelationExists($relation);

        $this->relatedData[$relation]['update']->push([
            'id'    => $id,
            'pivot' => $pivotData,
        ]);

        return $this;
    }

    /**
     * Removes an existing related item.
     *
     * @param string $relation
     * @param integer $id
     * @return DataSyncCollector $this
     */
    public function removeRelation($relation, $id) {
        if (app()->environment('testing')) {
            return $this;
        }

        $this->checkRelationExists($relation);

        $this->relatedData[$relation]['remove']->push($id);

        return $this;
    }

    /**
     * Sets the identifier (used for create actions)
     *
     * @param integer|string $id
     * @return DataSyncCollector $this
     */
    public function identifier($id) {
        if (app()->environment('testing')) {
            return $this;
        }

        $this->identifier = $id;

        return $this;
    }

    public function getConnections() {
        return $this->connections;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function getFiles() {
        return $this->files;
    }

    public function getSyncName() {
        return $this->syncName;
    }

    public function getIdentifier() {
        return $this->identifier;
    }

    public function getRelatedData() {
        return $this->relatedData;
    }

    public function getCustomActions() {
        return $this->customActions;
    }

    public function getAction() {
        return $this->action;
    }

    public function getModel() {
        return $this->model;
    }

    public function shouldLog() {
        return $this->loggingEnabled;
    }

    /**
     * Makes sure the given relation exists as an array.
     *
     * @param string $relation
     */
    protected function checkRelationExists($relation) {
        if (empty($this->relatedData[$relation])) {
            $this->relatedData[$relation] = [
                'add'       => collect([]),
                'update'    => collect([]),
                'remove'    => collect([]),
            ];
        }
    }
}

<?php

namespace Baufragen\DataSync\Traits;

use Baufragen\DataSync\DataSyncLog;
use Baufragen\DataSync\Exceptions\ConfigNotFoundException;
use Baufragen\DataSync\Exceptions\RelationNotFoundException;
use Baufragen\DataSync\Helpers\DataSyncAction;
use Illuminate\Support\Facades\DB;

trait HasDataSync {

    protected static $dataSyncTemporarilyDisabled = false;

    public static function bootHasDataSync() {

        static::created(function ($model) {

            if (!static::$dataSyncTemporarilyDisabled) {
                app('dataSync.handler')->executeSync(DataSyncAction::CREATE, $model);
            }

        });

        static::updated(function ($model) {

            if (!static::$dataSyncTemporarilyDisabled) {
                app('dataSync.handler')->executeSync(DataSyncAction::UPDATE, $model);
            }

        });

        static::deleted(function ($model) {

            if (!static::$dataSyncTemporarilyDisabled) {
                app('dataSync.handler')->executeSync(DataSyncAction::DELETE, $model);
            }

        });

    }

    public function dataSyncLogs() {
        return $this->hasMany(DataSyncLog::class, 'identifier')
                ->where('model', $this->getSyncName())
                ->orderBy('created_at', 'DESC');
    }

    /**
     * By default the class name is returned but this can be overwritten in order to sync with
     * a different model class on the other end.
     *
     * @return string
     */
    public function getSyncName() {
        return static::class;
    }

    /**
     * Get all fields that should be synced.
     * Those can be configured via the "synced_fields" property, otherwise everything will be synced.
     *
     * @return array
     */
    public function getSyncedFields() {
        if (property_exists($this, 'synced_fields')) {
            $syncedFields = $this->synced_fields;
        } else {
            $syncedFields = array_keys($this->attributes);
        }

        return $syncedFields;
    }

    /**
     * Get all data that has changed and should be synced.
     *
     * @return array
     */
    public function getSyncableData() {
        $allFields = $this->getDirty();

        if (empty($allFields)) {
            $allFields = $this->getAttributes();
        }

        $changedFields = array_intersect_key($allFields, array_flip($this->getSyncedFields()));

        return $changedFields;
    }

    /**
     * Can be overwritten to customize the syncable data (change values etc).
     *
     * @return array
     */
    public function customizeSyncableData() {
        return $this->getSyncableData();
    }

    /**
     * Handles the decision which execution method to call.
     *
     * @param DataSyncAction $action
     * @param array|null $data
     * @return mixed
     */
    public function handleDataSync(DataSyncAction $action, $data, $identifier = null) {
        switch ($action->action) {
            case DataSyncAction::CREATE:
                return $this->handleDataSyncCreate($data, $identifier);
                break;

            case DataSyncAction::UPDATE:
                return $this->handleDataSyncUpdate($data);
                break;

            case DataSyncAction::DELETE:
                return $this->handleDataSyncDelete($data);
                break;
        }

        return false;
    }

    /**
     * Handles creation of new models.
     *
     * @param array $data
     * @return mixed
     */
    public function handleDataSyncCreate($data, $identifier = null) {
        $model = new static($data);
        if ($identifier) {
            $model->id = $identifier;
        }
        $model->saveWithoutDataSync();

        return $model;
    }

    /**
     * Handles updating data in existing models.
     *
     * @param array $data
     * @return mixed
     */
    public function handleDataSyncUpdate($data) {
        $this->fill($data);
        $this->saveWithoutDataSync();

        return $this;
    }

    /**
     * Handles deleting models.
     *
     * @param array $data
     * @return bool
     */
    public function handleDataSyncDelete($data) {
        $this->deleteWithoutDataSync();

        return true;
    }

    /**
     * Can be overwritten with rules for validation before dataSyncing is handled.
     *
     * @return array
     */
    public function dataSyncValidationRules() {
        return [];
    }

    /**
     * Can be overwritten to configure the endpoints this model should sync to.
     * By default it will sync to all endpoints in the datasync config file.
     *
     * @return array
     */
    public function dataSyncConnections() {
        $connections = config('datasync.connections');

        if (!$connections) {
            throw new ConfigNotFoundException("No config found for Model " . $this->getSyncName());
        }

        return array_keys($connections);
    }

    /**
     * Can be overwritten to configure which relationships (including pivot data) should
     * be synced automatically.
     *
     * @return array
     */
    public function dataSyncRelationships() {
        return [];
    }

    /**
     * Gathers all related data (depending on dataSyncRelationships method).
     *
     * @return null|array
     */
    public function getSyncedRelationData() {
        $syncedRelationships = $this->dataSyncRelationships();

        if (empty($syncedRelationships)) {
            return null;
        }

        return collect($syncedRelationships)->map(function ($relationship) {
            return $this->getSyncedRelationDataForRelationship($relationship);
        })->filter(function ($data) {
            return !empty($data);
        })->toArray();
    }

    /**
     *
     * @param string $relationship
     *
     * @return null|array
     */
    public function getSyncedRelationDataForRelationship($relationship) {
        if (!method_exists($this, $relationship)) {
            throw new RelationNotFoundException("Relation " . $relationship . " not found on Model " . static::class);
        }

        $relation = $this->$relationship();

        if ($relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany || $relation instanceof \Staudenmeir\EloquentEagerLimit\Relations\HasMany) {
            return [
                'name' => $relationship,
                'type' => 'hasMany',
                'data' => $relation->select('id')->pluck('id')->toArray(),
            ];
        }
        else if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo || $relation instanceof \Staudenmeir\EloquentEagerLimit\Relations\BelongsTo) {
            return [
                'name' => $relationship,
                'type' => 'belongsTo',
                'data' => $relation->first()->id,
            ];
        }
        else if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany || $relation instanceof \Staudenmeir\EloquentEagerLimit\Relations\BelongsToMany) {
            return [
                'name' => $relationship,
                'type' => 'belongsToMany',
                'data' => DB::table($relation->getTable())->where($relation->getForeignPivotKeyName(), $this->id)->get()->map(function ($row) use ($relation) {
                    $row = (array)$row;

                    return [
                        'id'    => $row[$relation->getRelatedPivotKeyName()],
                        'data'  => array_intersect_key($row, array_flip(array_diff(array_keys($row), [$relation->getRelatedPivotKeyName(), $relation->getForeignPivotKeyName()]))),
                    ];
                })->toArray(),
            ];
        }

        return null;
    }

    public static function disableDataSync() {
        static::$dataSyncTemporarilyDisabled = true;
    }

    public static function enableDataSync() {
        static::$dataSyncTemporarilyDisabled = false;
    }

    public function saveWithoutDataSync() {
        static::disableDataSync();
        $this->save();
        static::enableDataSync();

        return $this;
    }

    public function deleteWithoutDataSync() {
        static::disableDataSync();
        $this->delete();
        static::enableDataSync();
    }

    public function dataSyncShouldBeLogged() {
        return !property_exists($this, 'dataSyncLoggingDisabled') || $this->dataSyncLoggingDisabled === false;
    }

    public function needsInitialDataSync() {
        return !$this->dataSyncLogs()->successful()->exists();
    }

    public function needsManualDataSync() {
        $latestSync = $this->dataSyncLogs()->successful()->first();

        return $latestSync->created_at->diffInSeconds($this->updated_at) > 5;
    }

    public function triggerInitialDataSync() {
        app('dataSync.handler')->executeSync(DataSyncAction::CREATE, $this);
    }

    public function triggerManualDataSync() {
        app('dataSync.handler')->executeSync(DataSyncAction::UPDATE, $this);
    }

}

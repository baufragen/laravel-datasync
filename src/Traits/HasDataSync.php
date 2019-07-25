<?php

namespace Baufragen\DataSync\Traits;

use Baufragen\DataSync\Exceptions\ConfigNotFoundException;
use Baufragen\DataSync\Helpers\DataSyncAction;
use Baufragen\DataSync\Jobs\HandleDataSync;

trait HasDataSync {

    public static function bootHasDataSync() {

        app('dataSync.container')->registerModel((new static())->getSyncName(), static::class);

        static::created(function ($model) {

            app('dataSync.handler')->executeSync(DataSyncAction::CREATE, $model);

        });

        static::updated(function ($model) {

            app('dataSync.handler')->executeSync(DataSyncAction::UPDATE, $model);

        });

        static::deleted(function ($model) {

            app('dataSync.handler')->executeSync(DataSyncAction::DELETE, $model);

        });

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
        $changedFields = array_intersect_key($this->getDirty(), array_flip($this->getSyncedFields()));

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
    public function handleDataSync(DataSyncAction $action, $data) {
        switch ($action->action) {
            case DataSyncAction::CREATE:
                return $this->handleDataSyncCreate($data);
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
    public function handleDataSyncCreate($data) {
        return static::create($data);
    }

    /**
     * Handles updating data in existing models.
     *
     * @param array $data
     * @return mixed
     */
    public function handleDataSyncUpdate($data) {
        $this->fill($data);
        $this->save();

        return $this;
    }

    /**
     * Handles deleting models.
     *
     * @param array $data
     * @return bool
     */
    public function handleDataSyncDelete($data) {
        $this->delete();

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

}

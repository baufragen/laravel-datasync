<?php

namespace Baufragen\DataSync\Traits;

use Baufragen\DataSync\DataSyncLog;
use Baufragen\DataSync\Helpers\DataSyncAction;
use Baufragen\DataSync\Helpers\DataSyncCollector;
use Baufragen\DataSync\Helpers\DataSyncTransformer;
use Baufragen\DataSync\Jobs\HandleDataSync;

trait HasDataSync {

    protected $dataSyncEnabled = true;

    public static function bootHasDataSync() {

        static::created(function ($model) {
            if ($model->dataSyncEnabled) {
                /** @var DataSyncCollector $collector */
                $collector = new DataSyncCollector(new DataSyncAction(DataSyncAction::CREATE));
                $collector
                    ->initForModel($model);
                $model->beforeDataSync($collector);

                HandleDataSync::dispatch($collector);
            }
        });

        static::updated(function ($model) {
            if ($model->dataSyncEnabled) {
                /** @var DataSyncCollector $collector */
                $collector = new DataSyncCollector(new DataSyncAction(DataSyncAction::CREATE));
                $collector
                    ->initForModel($model)
                    ->identifier($model->id);
                $model->beforeDataSync($collector);

                HandleDataSync::dispatch($collector);
            }
        });

        static::deleted(function ($model) {
            if ($model->dataSyncEnabled) {
                /** @var DataSyncCollector $collector */
                $collector = new DataSyncCollector(new DataSyncAction(DataSyncAction::CREATE));
                $collector
                    ->initForModel($model)
                    ->identifier($model->id);
                $model->beforeDataSync($collector);

                HandleDataSync::dispatch($collector);
            }
        });

    }

    public function dataSyncLogs() {
        return $this->hasMany(DataSyncLog::class, 'identifier')
            ->where('model', $this->getSyncName())
            ->orderBy('created_at', 'DESC');
    }

    /**
     * Can be overwritten in order to manipulate the collector before it
     * is sent to the endpoints (add files, relatedData, transform attributes etc).
     *
     * @param DataSyncCollector $collector
     * @return void
     */
    public function beforeDataSync(DataSyncCollector $collector) {
        return;
    }

    public function getSyncedAttributeData() {
        $allFields = $this->getDirty();

        if (empty($allFields)) {
            $allFields = $this->getAttributes();
        }

        $changedFields = array_intersect_key($allFields, array_flip($this->getSyncedFields()));

        return $changedFields;
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
     * By default the class name is returned but this can be overwritten in order to sync with
     * a different model class on the other end.
     *
     * @return string
     */
    public function getSyncName() {
        return static::class;
    }

    /**
     * Use the dataSyncLoggingDisabled property to disable log messages for this model.
     *
     * @return bool
     */
    public function dataSyncShouldBeLogged() {
        return !property_exists($this, 'dataSyncLoggingDisabled') || $this->dataSyncLoggingDisabled === false;
    }

    /**
     * Executes the actual syncing of the data.
     *
     * @param DataSyncTransformer $transformer
     */
    final public function executeDataSync(DataSyncTransformer $transformer) {
        $this->disableDataSync();

        $attributes = $this->beforeDataSyncAttributeUpdate($transformer->getAttributes());
        $this->executeAttributeDataSync($attributes);

        $relationData = $this->beforeDataSyncRelationUpdate($transformer->getRelations());
        $this->executeRelationDataSync($relationData);

        $files = $this->beforeDataSyncFilesUpdate($transformer->getFiles());
        $this->executeFilesDataSync($files);

        $this->enableDataSync();
    }

    public function enableDataSync() {
        $this->dataSyncEnabled = true;
    }

    public function disableDataSync() {
        $this->dataSyncEnabled = false;
    }

    /**
     * Can be overwritten to change the attributes before they are update on the model.
     *
     * @param array $attributes
     * @return array
     */
    protected function beforeDataSyncAttributeUpdate($attributes) {
        return $attributes;
    }

    /**
     * Can be overwritten to change relation data before the update is handled.
     *
     * @param array $relations
     * @return array
     */
    protected function beforeDataSyncRelationUpdate($relations) {
        return $relations;
    }

    /**
     * Can be overwritten to change files data before the update is handled.
     *
     * @param array $files
     * @return array
     */
    protected function beforeDataSyncFilesUpdate($files) {
        return $files;
    }

    protected function executeAttributeDataSync($attributes) {
        if (empty($attributes)) {
            return;
        }

        foreach ($attributes as $attribute => $value) {
            $this->{$attribute} = $value;
        }

        // TODO: save without sync
        $this->save();
    }

    protected function executeRelationDataSync($relations) {
        if (empty($relations)) {
            return;
        }

        foreach ($relations as $relation => $changes) {
            if (!empty($changes['add'])) {
                foreach ($changes['add'] as $addedChange) {
                    $this->addChangedRelationship($relation, $addedChange);
                }
            }

            if (!empty($changes['update'])) {
                foreach ($changes['update'] as $updatedChange) {
                    $this->updateChangedRelationship($relation, $updatedChange);
                }
            }

            if (!empty($changes['remove'])) {
                foreach ($changes['remove'] as $removedChange) {
                    $this->removeChangedRelationship($relation, $removedChange);
                }
            }
        }
    }

    protected function executeFilesDataSync($files) {
        return;
    }

    protected function addChangedRelationship($relation, $change) {
        $this->{$relation}()->attach($change['id'], $change['pivot']);
    }

    protected function updateChangedRelationship($relation, $change) {
        $this->{$relation}()->updateExistingPivot($change['id'], $change['pivot']);
    }

    protected function removeChangedRelationship($relation, $change) {
        $this->{$relation}()->detach($change['id']);
    }

}

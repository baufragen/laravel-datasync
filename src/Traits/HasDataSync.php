<?php

namespace Baufragen\DataSync\Traits;

use Baufragen\DataSync\Collectors\AllAttributeCollector;
use Baufragen\DataSync\Collectors\AttributeCollector;
use Baufragen\DataSync\Collectors\ChangedAttributeCollector;
use Baufragen\DataSync\Interfaces\DataSyncCollecting;
use Baufragen\DataSync\Collectors\FileCollector;
use Baufragen\DataSync\DataSyncLog;
use Baufragen\DataSync\Helpers\DataSyncAction;
use Baufragen\DataSync\Interfaces\DataSyncing;
use Baufragen\DataSync\Transformers\AttributeTransformer;
use Baufragen\DataSync\Transformers\RawTransformer;
use Illuminate\Http\UploadedFile;

trait HasDataSync {

    protected $dataSyncEnabled = true;

    public static function bootHasDataSync() {

        static::created(function (DataSyncing $model) {
            if ($model->automaticDataSyncEnabled()) {
                dataSync($model, AllAttributeCollector::class, new DataSyncAction(DataSyncAction::CREATE));
            }
        });

        static::updated(function (DataSyncing $model) {
            if ($model->automaticDataSyncEnabled()) {
                dataSync($model, ChangedAttributeCollector::class, new DataSyncAction(DataSyncAction::UPDATE));
            }
        });

        static::deleted(function (DataSyncing $model) {
            if ($model->automaticDataSyncEnabled()) {
                // TODO: implement deletion sync
            }
        });

    }

    public function dataSyncLogs() {
        return $this->hasMany(DataSyncLog::class, 'identifier')
            ->where('model', $this->getSyncName())
            ->orderBy('created_at', 'DESC');
    }

    public function beforeDataSync(DataSyncCollecting $collector) {
        return true;
    }

    public function getDirtySyncedAttributeData() {
        $allFields = $this->getDirty();

        if (empty($allFields)) {
            $allFields = $this->getAttributes();
        }

        $changedFields = array_intersect_key($allFields, array_flip($this->getSyncedFields()));

        return $changedFields;
    }

    public function getAllSyncedAttributeData() {
        $allFields = $this->getAttributes();

        return array_intersect_key($allFields, array_flip($this->getSyncedFields()));
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

    public function enableDataSync() {
        $this->dataSyncEnabled = true;
    }

    public function disableDataSync() {
        $this->dataSyncEnabled = false;
    }

    /**
     * @return bool
     */
    public function dataSyncEnabled() {
        return $this->dataSyncEnabled;
    }

    public function automaticDataSyncEnabled() {
        return $this->dataSyncEnabled() && (!property_exists($this, "disableAutomaticDataSync") || $this->disableAutomaticDataSync === false);
    }

    public function executeAttributeDataSync(AttributeTransformer $transformer) {
        $this->disableDataSync();

        $attributes = $transformer->getAttributes();

        if (empty($attributes)) {
            return;
        }

        foreach ($attributes as $attribute => $value) {
            $this->{$attribute} = $value;
        }

        $this->save();

        $this->enableDataSync();
    }

    public function executeFileDataSync(string $fileName, UploadedFile $file) {
        return;
    }

    public function executeFileDataSyncDeletion(string $fileName) {
        return;
    }

    public function executeRawDataSync(RawTransformer $transformer) {
        return;
    }

    public function executeActionDataSync(string $action, $additionalData = null) {
        return;
    }

    public function beforeDataSyncAttributes(AttributeCollector $collector) {
        return;
    }

    public function beforeDataSyncFiles(FileCollector $collector) {
        return;
    }

    public function getRawSyncData() {
        return null;
    }

    public function executeRelationSyncAttach(string $relation, $id, array $pivotData) {
        if (method_exists($this, $relation)) {
            if ($this->{$relation}()->where('id', $id)->exists()) {
                $this->executeRelationSyncUpdate($relation, $id, $pivotData);
            } else {
                $this->{$relation}()->attach($id, $pivotData);
            }
        }
    }

    public function executeRelationSyncDetach(string $relation, $id, array $pivotData) {
        if (method_exists($this, $relation)) {
            $this->{$relation}()->detach($id, $pivotData);
        }
    }

    public function executeRelationSyncUpdate(string $relation, $id, array $pivotData) {
        if (method_exists($this, $relation)) {
            if (!$this->{$relation}()->where('id', $id)->exists()) {
                $this->executeRelationSyncAttach($relation, $id, $pivotData);
            } else {
                $this->{$relation}()->updateExistingPivot($id, $pivotData);
            }
        }
    }

    public function executeRelationSyncReset(string $relation, array $relationData) {
        if (method_exists($this, $relation)) {
            $this->{$relation}->sync($relationData);
        }
    }

}

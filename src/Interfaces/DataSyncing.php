<?php

namespace Baufragen\DataSync\Interfaces;

use Baufragen\DataSync\Interfaces\DataSyncCollecting;
use Baufragen\DataSync\Collectors\AttributeCollector;
use Baufragen\DataSync\Collectors\FileCollector;
use Baufragen\DataSync\Transformers\AttributeTransformer;
use Baufragen\DataSync\Transformers\RawTransformer;
use Illuminate\Http\UploadedFile;

interface DataSyncing {
    public function getSyncName();
    public function automaticDataSyncEnabled();
    public function beforeDataSync(DataSyncCollecting $collector);
    public function beforeDataSyncAttributes(AttributeCollector $collector);
    public function beforeDataSyncFiles(FileCollector $collector);
    public function getDirtySyncedAttributeData();
    public function getAllSyncedAttributeData();
    public function executeAttributeDataSync(AttributeTransformer $transformer);
    public function executeFileDataSync(string $fileName, UploadedFile $file);
    public function executeFileDataSyncDeletion(string $fileName);
    public function executeRawDataSync(RawTransformer $transformer);
    public function executeActionDataSync(string $action, $additionalData = null);
    public function executeRelationSyncAttach(string $relation, $id, array $pivotData);
    public function executeRelationSyncDetach(string $relation, $id, array $pivotData);
    public function executeRelationSyncUpdate(string $relation, $id, array $pivotData);
    public function executeRelationSyncReset(string $relation, array $relationData);
}

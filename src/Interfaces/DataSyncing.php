<?php

namespace Baufragen\DataSync\Interfaces;

use Baufragen\DataSync\Collectors\DataSyncCollecting;
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
    public function executeRawDataSync(RawTransformer $transformer);
}

<?php

namespace Baufragen\DataSync\Interfaces;

use Baufragen\DataSync\Collectors\DataSyncCollecting;
use Baufragen\DataSync\Collectors\AttributeCollector;
use Baufragen\DataSync\Collectors\FileCollector;

interface DataSyncing {
    public function getSyncName();
    public function automaticDataSyncEnabled();
    public function beforeDataSync(DataSyncCollecting $collector);
    public function beforeDataSyncAttributes(AttributeCollector $collector);
    public function beforeDataSyncFiles(FileCollector $collector);
    public function getDirtySyncedAttributeData();
    public function getAllSyncedAttributeData();
    public function executeAttributeDataSync();
    public function executeFileDataSync();
    public function executeRawDataSync();
}

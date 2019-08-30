<?php

namespace Baufragen\DataSync\Interfaces;

use Baufragen\DataSync\Collectors\DataSyncCollecting;
use Baufragen\DataSync\Helpers\AttributeCollector;
use Baufragen\DataSync\Helpers\FileCollector;

interface DataSyncing {
    public function getSyncName();
    public function automaticDataSyncEnabled();
    public function beforeDataSync(DataSyncCollecting $collector);
    public function beforeDataSyncAttributes(AttributeCollector $collector);
    public function beforeDataSyncFiles(FileCollector $collector);
    public function getRawSyncData();
    public function getDirtySyncedAttributeData();
    public function getAllSyncedAttributeData();
}

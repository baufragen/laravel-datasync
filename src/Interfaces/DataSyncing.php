<?php

namespace Baufragen\DataSync\Interfaces;

use Baufragen\DataSync\Helpers\DataSyncCollector;

interface DataSyncing {
    public function getSyncName();
    public function automaticDataSyncEnabled();
    public function beforeDataSync(DataSyncCollector $collector);
}

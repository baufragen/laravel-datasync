<?php

namespace Baufragen\DataSync\Interfaces;

interface DataSyncing {
    public function getSyncName();
    public function automaticDataSyncEnabled();
    public function beforeDataSync();
}

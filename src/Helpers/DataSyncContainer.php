<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Exceptions\SyncedModelNotFoundException;

class DataSyncContainer {
    protected $registeredModels = [];

    public function __construct()
    {
        $this->registeredModels = config('datasync.models');
    }

    public function getClassBySyncName($syncName) {
        foreach ($this->registeredModels as $class => $loopSyncName) {
            if ($syncName === $loopSyncName) {
                return $class;
            }
        }

        throw new SyncedModelNotFoundException("Could not find registered model for " . $syncName);
    }

    public function getSyncNameByClass($class) {
        if (!$this->registeredModels[$class]) {
            throw new SyncedModelNotFoundException("Could not find registered model for " . $class);
        }

        return $this->registeredModels[$class];
    }
}

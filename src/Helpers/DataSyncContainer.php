<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Exceptions\SyncedModelNotFoundException;

class DataSyncContainer {
    protected $registeredModels = [];

    public function registerModel($syncName, $class) {
        if (empty($this->registeredModels[$syncName])) {
            $this->registeredModels[$syncName] = $class;
        }
    }

    public function getClassBySyncName($syncName) {
        if (empty($this->registeredModels[$syncName])) {
            throw new SyncedModelNotFoundException("Could not find registered model for " . $syncName);
        }

        return $this->registeredModels[$syncName];
    }
}

<?php

namespace Baufragen\DataSync\Helpers;

class DataSyncAction {
    const CREATE            = 'create';
    const UPDATE            = 'update';
    const DELETE            = 'delete';
    const UPDATEORCREATE    = 'updateorcreate';
    const DUMMY             = 'dummy';

    public $action;

    public function __construct($action) {
        $this->action = $action;
    }

    public function isCreate() {
        return $this->action == self::CREATE;
    }

    public function isUpdateOrCreate() {
        return $this->action == self::UPDATEORCREATE;
    }

    public function isUpdate() {
        return $this->isUpdateOrCreate() || $this->action == self::UPDATE;
    }

    public function isDelete() {
        return $this->action == self::DELETE;
    }

    public function isDummy() {
        return $this->action == self::DUMMY;
    }

    public function __toString() {
        return $this->action;
    }
}

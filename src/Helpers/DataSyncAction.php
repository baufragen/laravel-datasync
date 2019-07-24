<?php

namespace Baufragen\DataSync\Helpers;

class DataSyncAction {
    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';

    public $action;

    public function __construct($action) {
        $this->action = $action;
    }

    public function isCreate() {
        return $this->action == self::CREATE;
    }

    public function isUpdate() {
        return $this->action == self::UPDATE;
    }

    public function isDelete() {
        return $this->action == self::DELETE;
    }
}

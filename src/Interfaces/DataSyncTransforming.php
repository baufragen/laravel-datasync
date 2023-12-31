<?php

namespace Baufragen\DataSync\Interfaces;

interface DataSyncTransforming {
    public function validate();
    public function sync();

    public function getModel();
    public function addHook(string $type, callable $callback);
}
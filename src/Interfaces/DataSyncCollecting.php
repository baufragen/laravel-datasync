<?php

namespace Baufragen\DataSync\Interfaces;

use Baufragen\DataSync\Helpers\DataSyncConnection;

interface DataSyncCollecting {
    const HOOK_AFTER_SYNC = 'aftersync';

    public function getSyncName();
    public function getIdentifier();
    public function getConnections();
    public function transform(DataSyncConnection $connection);
    public function getType();
    public function getModel();
    public function shouldLog();
    public function hasHooks(string $name);
    public function getHooks(string $name);
}
<?php

namespace Baufragen\DataSync\Collectors;

use Baufragen\DataSync\Helpers\DataSyncConnection;

interface DataSyncCollecting {
    public function getSyncName();
    public function getIdentifier();
    public function getConnections();
    public function transform(DataSyncConnection $connection);
    public function getType();
    public function getModel();
    public function shouldLog();
}
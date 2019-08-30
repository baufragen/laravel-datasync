<?php

namespace Baufragen\DataSync\Collectors;

interface DataSyncTransforming {
    public function validate();
    public function sync();
}
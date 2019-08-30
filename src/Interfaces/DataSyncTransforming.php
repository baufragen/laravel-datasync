<?php

namespace Baufragen\DataSync\Interfaces;

interface DataSyncTransforming {
    public function validate();
    public function sync();
}
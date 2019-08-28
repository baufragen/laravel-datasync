<?php

namespace Baufragen\DataSync\Tests;

class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app) {
        return [
            Baufragen\DataSync\DataSyncServiceProvider::class,
        ];
    }
}

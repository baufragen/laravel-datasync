<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Exceptions\ConfigNotFoundException;
use GuzzleHttp\Client;

class DataSyncClient extends Client {
    public function __construct($connection)
    {
        $connectionDetails = config('datasync.connections.' . $connection);

        if (empty($connectionDetails)) {
            throw new ConfigNotFoundException("Config for connection " . $connection . " could not be found");
        }

        $config = [
            'base_uri' => $connectionDetails['baseurl'],
        ];

        parent::__construct($config);
    }
}

<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Exceptions\ConfigNotFoundException;
use GuzzleHttp\Client;

class DataSyncClient extends Client {
    public function __construct(DataSyncConnection $connection)
    {
        $config = [
            'base_uri'  => $connection->getBaseUrl(),
            'verify'    => false, // TODO: make this configurable
        ];

        parent::__construct($config);
    }
}

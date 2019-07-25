<?php

return [

    'own_connection'    => env('DATASYNC_OWN_CONNECTION'),

    /**
     * Connections can be configured to allow models to be synced to multiple endpoints.
     * They can be defined with the dataSyncConnections method of a model.
     */
    'connections'       => [

        'example' => [
            'baseurl'   => 'https://api.example.com/',
            'apikey'    => '123456789',
            'secret'    => '123456789',
            'enabled'   => true,
            'encrypted' => true,
        ],

    ],

];

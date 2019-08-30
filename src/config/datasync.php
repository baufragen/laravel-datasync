<?php

return [

    'models'    => [
        App\User::class => 'user',
    ],

    'own_connection'    => env('DATASYNC_OWN_CONNECTION'),

    /**
     * Connections can be configured to allow models to be synced to multiple endpoints.
     * They can be defined with the dataSyncConnections method of a model.
     */
    'connections'       => [

        'example' => [
            'baseurl'   => 'https://api.example.com/',
            'apikey'    => '123456789',
            'enabled'   => true,
            'encrypted' => true,
        ],

    ],

    'collectors'    => [
        'attribute-all'     => Baufragen\DataSync\Collectors\AllAttributeCollector::class,
        'attribute-changed' => Baufragen\DataSync\Collectors\ChangedAttributeCollector::class,
        'raw'               => Baufragen\DataSync\Collectors\RawCollector::class,
        'file'              => Baufragen\DataSync\Collectors\FileCollector::class,
        'action'            => Baufragen\DataSync\Collectors\ActionCollector::class,
        'dummy'             => Baufragen\DataSync\Collectors\DummyCollector::class,
    ],

    'transformers'  => [
        'attribute' => Baufragen\DataSync\Transformers\AttributeTransformer::class,
        'raw'       => Baufragen\DataSync\Transformers\RawTransformer::class,
        'file'      => Baufragen\DataSync\Transformers\FileTransformer::class,
        'action'    => Baufragen\DataSync\Transformers\ActionTransformer::class,
    ]

];

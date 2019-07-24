<?php

namespace Baufragen\DataSync\Jobs;

use Baufragen\DataSync\Exceptions\DataSyncRequestFailedException;
use Baufragen\DataSync\Helpers\DataSyncClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleDataSync implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $connection;
    protected $data;
    protected $syncName;
    protected $identifier;
    protected $action;

    public function __construct($connection, $syncName, $data, $action, $identifier = null) {
        $this->connection   = $connection;
        $this->syncName     = $syncName;
        $this->identifier   = $identifier;
        $this->data         = $data;
        $this->action       = $action;
    }

    public function handle() {
        $client = new DataSyncClient($this->connection);

        $response = $client->post(route('dataSync.handle', [], false), [
            'form_params' => [
                'model'         => $this->syncName,
                'identifier'    => $this->identifier,
                'action'        => $this->action,
                'data'          => $this->data,
            ],
        ]);

        if ($response->getStatusCode() !== 200 || $response->getStatusCode() !== 201) {
            throw new DataSyncRequestFailedException("DataSync Request failed (" . $response->getStatusCode() . "): " . $response->getReasonPhrase());
        }
    }
}
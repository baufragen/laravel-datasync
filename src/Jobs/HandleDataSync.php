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

    protected $dataSyncConnection;
    protected $data;
    protected $syncName;
    protected $identifier;
    protected $action;
    protected $encrypted;

    public function __construct($dataSyncConnection, $syncName, $data, $action, $identifier = null) {
        $this->dataSyncConnection   = $dataSyncConnection;
        $this->syncName     = $syncName;
        $this->identifier   = $identifier;
        $this->data         = $data;
        $this->action       = $action;
        $this->encrypted    = !empty(config('datasync.connections.' . $dataSyncConnection . '.encrypted'));
    }

    public function handle() {
        $client = new DataSyncClient($this->dataSyncConnection);

        $response = $client->post(route('dataSync.handle', [], false), [
            'form_params' => [
                'connection'    => config('datasync.own_connection'),
                'model'         => $this->syncName,
                'identifier'    => $this->identifier,
                'action'        => $this->action,
                'data'          => $this->encrypted ? encrypt($this->data) : $this->data,
            ],
        ]);

        if ($response->getStatusCode() !== 200 || $response->getStatusCode() !== 201) {
            throw new DataSyncRequestFailedException("DataSync Request failed (" . $response->getStatusCode() . "): " . $response->getReasonPhrase());
        }
    }
}
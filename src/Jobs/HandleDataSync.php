<?php

namespace Baufragen\DataSync\Jobs;

use Baufragen\DataSync\DataSyncLog;
use Baufragen\DataSync\Exceptions\DataSyncRequestFailedException;
use Baufragen\DataSync\Helpers\DataSyncClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Exception\RequestException;

class HandleDataSync implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dataSyncConnection;
    protected $apiKey;
    protected $data;
    protected $syncName;
    protected $relationdata;
    protected $identifier;
    protected $action;
    protected $encrypted;
    protected $shouldLog;

    public function __construct($dataSyncConnection, $syncName, $data, $action, $relationdata, $identifier = null, $shouldLog = true) {
        $this->dataSyncConnection   = $dataSyncConnection;
        $this->apiKey       = config('datasync.connections.' . $dataSyncConnection . ".apikey");
        $this->syncName     = $syncName;
        $this->relationdata    = $relationdata;
        $this->identifier   = $identifier;
        $this->data         = $data;
        $this->action       = $action;
        $this->shouldLog    = $shouldLog;
        $this->encrypted    = !empty(config('datasync.connections.' . $dataSyncConnection . '.encrypted'));
    }

    public function handle() {
        $client = new DataSyncClient($this->dataSyncConnection);

        try {
            $response = $client->post(route('dataSync.handle', [], false), [
                'form_params' => [
                    'connection'    => config('datasync.own_connection'),
                    'apikey'        => $this->apiKey,
                    'model'         => $this->syncName,
                    'identifier'    => $this->identifier,
                    'action'        => $this->action,
                    'data'          => $this->encrypted ? encrypt($this->data) : $this->data,
                    'relationdata'  => $this->relationdata,
                ],
            ]);

            if ($response->getStatusCode() === 200 || $response->getStatusCode === 201) {
                if ($this->shouldLog) {
                    DataSyncLog::succeeded($this->action, $this->syncName, $this->identifier, $this->dataSyncConnection);
                }
            }
        } catch (RequestException $e) {
            // errors always get logged
            DataSyncLog::failed($this->action, $this->syncName, $this->identifier, $this->dataSyncConnection, $this->data, $e->getResponse());

            throw new DataSyncRequestFailedException("DataSync Request failed (" . $e->getResponse()->getStatusCode() . "): " . $e->getResponse()->getReasonPhrase());
        } catch (\Exception $e) {
            throw new DataSyncRequestFailedException("DataSync Request failed with Exception: " . $e->getMessage());
        }
    }
}
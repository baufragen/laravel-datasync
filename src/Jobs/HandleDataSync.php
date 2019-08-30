<?php

namespace Baufragen\DataSync\Jobs;

use Baufragen\DataSync\Collectors\DataSyncCollecting;
use Baufragen\DataSync\DataSyncLog;
use Baufragen\DataSync\Exceptions\DataSyncRequestFailedException;
use Baufragen\DataSync\Helpers\DataSyncClient;
use Baufragen\DataSync\Helpers\DataSyncConnection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Exception\RequestException;

class HandleDataSync implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var DataSyncCollecting */
    protected $dataCollector;

    public function __construct(DataSyncCollecting $collector) {
        $this->dataCollector = $collector;
    }

    public function handle() {
        $this->dataCollector
            ->getConnections()
            ->each(function(DataSyncConnection $connection) {
                $this->syncToConnection($connection);
            });
    }

    protected function syncToConnection(DataSyncConnection $connection) {
        $client = new DataSyncClient($connection);

        try {
            $payload = array_merge(
                [
                    [
                        'name'      => 'connection',
                        'contents'  => config('datasync.own_connection'),
                    ],
                    [
                        'name'      => 'apikey',
                        'contents'  => $connection->getApiKey(),
                    ],
                    [
                        'name'      => 'encrypted',
                        'contents'  => $connection->isEncrypted(),
                    ],
                    [
                        'name'      => 'model',
                        'contents'  => $this->dataCollector->getSyncName(),
                    ],
                    [
                        'name'      => 'identifier',
                        'contents'  => $this->dataCollector->getIdentifier(),
                    ],
                    [
                        'name'      => 'type',
                        'contents'  => $this->dataCollector->getType(),
                    ],
                ],
                $this->dataCollector->transform($connection)
            );

            $response = $client->post(route('dataSync.handle', [], false), [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'multipart' => $payload,
            ]);

            if (in_array($response->getStatusCode(), [200, 201])) {
                if ($this->dataCollector->shouldLog()) {
                    DataSyncLog::succeeded($this->dataCollector->getType(), $this->dataCollector->getSyncName(), $this->dataCollector->getIdentifier(), $connection, $payload, $response);
                }
            }
        } catch (RequestException $e) {
            // errors always get logged
            DataSyncLog::failed($this->dataCollector->getType(), $this->dataCollector->getSyncName(), $this->dataCollector->getIdentifier(), $connection, $payload, $e->getResponse());

            throw new DataSyncRequestFailedException("DataSync Request failed (" . $e->getResponse()->getStatusCode() . "): " . $e->getResponse()->getReasonPhrase());
        } catch (\Exception $e) {
            throw new DataSyncRequestFailedException("DataSync Request failed with Exception: " . $e->getMessage());
        }
    }
}
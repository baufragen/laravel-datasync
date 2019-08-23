<?php

namespace Baufragen\DataSync\Jobs;

use Baufragen\DataSync\DataSyncLog;
use Baufragen\DataSync\Exceptions\ConfigNotFoundException;
use Baufragen\DataSync\Exceptions\DataSyncRequestFailedException;
use Baufragen\DataSync\Helpers\DataSyncClient;
use Baufragen\DataSync\Helpers\DataSyncCollector;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Exception\RequestException;

class HandleDataSync implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var DataSyncCollector */
    protected $dataCollector;

    public function __construct(DataSyncCollector $collector) {
        $this->dataCollector = $collector;
    }

    public function handle() {
        $this->dataCollector
            ->getConnections()
            ->each(function($connection) {
                $this->syncToConnection($connection);
            });
    }

    protected function syncToConnection($connection) {
        $client = new DataSyncClient($connection);

        $config = config('datasync.connections.' . $connection);

        if (empty($config)) {
            throw new ConfigNotFoundException("Config not found for connection " . $connection);
        }

        $encrypted  = $config['encrypted'];
        $apiKey     = $config['apikey'];

        $attributes     = $this->getAttributesFromCollector($this->dataCollector, $encrypted);
        $files          = $this->getFilesFromCollector($this->dataCollector);
        $relationdata   = $this->getRelatedDataFromCollector($this->dataCollector, $encrypted);

        print_r(array_merge(
            [
                [
                    'name'      => 'connection',
                    'contents'  => config('datasync.own_connection'),
                ],
                [
                    'name'      => 'apikey',
                    'contents'  => $apiKey,
                ],
                [
                    'name'      => 'encrypted',
                    'contents'  => $encrypted,
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
                    'name'      => 'action',
                    'contents'  => (string)$this->dataCollector->getAction(),
                ],
            ],
            $attributes,
            $relationdata,
            $files
        ));
        exit;

        try {
            $response = $client->post(route('dataSync.handle', [], false), [
                'multipart' => array_merge(
                    [
                        [
                            'name'      => 'connection',
                            'contents'  => config('datasync.own_connection'),
                        ],
                        [
                            'name'      => 'apikey',
                            'contents'  => $apiKey,
                        ],
                        [
                            'name'      => 'encrypted',
                            'contents'  => $encrypted,
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
                            'name'      => 'action',
                            'contents'  => (string)$this->dataCollector->getAction(),
                        ],
                    ],
                    $attributes,
                    $relationdata,
                    $files
                ),
            ]);

            if (in_array($response->getStatusCode(), [200, 201])) {
                if ($this->dataCollector->shouldLog()) {
                    DataSyncLog::succeeded($this->dataCollector->getAction(), $this->dataCollector->getSyncName(), $this->dataCollector->getIdentifier(), $connection);
                }
            }
        } catch (RequestException $e) {
            // errors always get logged
            DataSyncLog::failed($this->dataCollector->getAction(), $this->dataCollector->getSyncName(), $this->dataCollector->getIdentifier(), $connection, $attributes, $e->getResponse());

            throw new DataSyncRequestFailedException("DataSync Request failed (" . $e->getResponse()->getStatusCode() . "): " . $e->getResponse()->getReasonPhrase());
        } catch (\Exception $e) {
            throw new DataSyncRequestFailedException("DataSync Request failed with Exception: " . $e->getMessage());
        }
    }

    protected function getAttributesFromCollector(DataSyncCollector $collector, $encrypted) {
        return collect($collector->getAttributes())
            ->when($encrypted, function ($attributes) {
                return $attributes->mapWithKeys(function ($attribute, $key) {
                    return [$key => encrypt($attribute)];
                });
            })
            ->map(function ($value, $key) {
                return [
                    'name' => 'data[' . $key . ']',
                    'contents' => $value,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function getFilesFromCollector(DataSyncCollector $collector) {
        return collect($collector->getFiles())
            ->map(function ($file, $name) {
                if (!file_exists($file['path'])) {
                    return null;
                }

                return array_merge(
                    [
                        'name'      => 'files[' . $name . ']',
                        'contents'  => fopen($file['path'], 'r'),
                    ],
                    !empty($file['fileName']) ? ['filename' => $file['fileName']] : [],
                    !empty($file['mimeType']) ? ['headers' => ['Content-Type' => $file['mimeType']]] : []
                );
            })
            ->filter(function ($file) {
                return !empty($file);
            })
            ->toArray();
    }

    protected function getRelatedDataFromCollector(DataSyncCollector $collector, $encrypted) {
        return collect($collector->getRelatedData())
            ->when($encrypted, function ($relations) {
                // TODO: implement encryption
                return $relations;
            })
            ->map(function ($value, $relation) {
                return [
                    'name' => 'relationdata[' . $relation . ']',
                    'contents' => $value,
                ];
            })
            ->values()
            ->toArray();
    }
}
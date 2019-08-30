<?php

namespace Baufragen\DataSync\Collectors;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncing;

abstract class BaseCollector {
    protected $connections;

    protected $syncName         = null;
    protected $identifier       = null;
    protected $loggingEnabled   = true;

    /** @var DataSyncing */
    protected $model;

    public function __construct() {
        $this->connections = collect();
    }

    public function setModel(DataSyncing $model) {
        $this->model = $model;

        $this->initializeSyncNameForModel($model);
        $this->initializeConnectionsForModel($model);
        $this->configureLoggingForModel($model);
    }

    public function initializeConnectionsForModel(DataSyncing $model) {
        if (method_exists($model, 'getSyncedConnections')) {
            $this->connections  = collect($model->getSyncedConnections())->map(function ($connection) {
                return new DataSyncConnection($connection);
            });
        } else {
            $this->connections  = collect(config('datasync.connections'))->keys()->map(function ($connection) {
                return new DataSyncConnection($connection);
            });
        }
    }

    public function configureLoggingForModel(DataSyncing $model) {
        $this->loggingEnabled = $model->dataSyncShouldBeLogged();
    }

    public function initializeSyncNameForModel(DataSyncing $model) {
        $this->syncName = $model->getSyncName();
    }

    public function identifier($id)
    {
        $this->identifier = $id;

        return $this;
    }

    public function getConnections() {
        return $this->connections;
    }

    public function getSyncName() {
        return $this->syncName;
    }

    public function getIdentifier() {
        return $this->identifier;
    }

    /**
     * @return DataSyncing
     */
    public function getModel() {
        return $this->model;
    }
}

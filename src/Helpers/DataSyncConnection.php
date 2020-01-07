<?php

namespace Baufragen\DataSync\Helpers;

use Baufragen\DataSync\Exceptions\ConfigNotFoundException;

class DataSyncConnection {
    protected $config;
    protected $name;

    public function __construct($connection) {
        $this->name     = $connection;
        $this->config   = config('datasync.connections.' . $connection);

        if (!$this->config) {
            throw new ConfigNotFoundException("No config found for connection " . $connection);
        }
    }

    public function isEncrypted() {
        return !empty($this->config['encrypted']);
    }

    public function getName() {
        return $this->name;
    }

    public function getBaseUrl() {
        return $this->config['baseurl'];
    }

    public function getApiKey() {
        return $this->config['apikey'];
    }

    public function hasAuth() {
        return !empty($this->config['auth_user']) && !empty($this->config['auth_password']);
    }

    public function getAuth() {
        return [
            $this->config['auth_user'],
            $this->config['auth_password'],
        ];
    }
}

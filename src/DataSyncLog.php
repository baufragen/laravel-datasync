<?php

namespace Baufragen\DataSync;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Illuminate\Database\Eloquent\Model;

class DataSyncLog extends Model
{
    protected $guarded = [];
    protected $modelNames = [];

    public function scopeSuccessful($query) {
        $query->where('successful', true);
    }

    public function scopeFailed($query) {
        $query->where('successful', false);
    }

    public function isSuccessful() {
        return $this->successful;
    }

    public function isFailed() {
        return !$this->successful;
    }

    public function getModelClass() {
        if (!empty($this->modelNames[$this->model])) {
            return $this->modelNames[$this->model];
        }

        $models = config('datasync.models');

        foreach ($models as $class => $name) {
            if ($name == $this->model) {
                $this->modelNames[$name] = $class;
                return $class;
            }
        }

        return "undefined";
    }

    public function getModelIdentifier() {
        $class = $this->getModelClass();

        if (!class_exists($class)) {
            return $this->identifier;
        }

        $model = $class::find($this->identifier);

        if (!$model) {
            return $this->identifier;
        }

        if (method_exists($model, "getDataSyncIdentifier")) {
            return $model->getDataSyncIdentifier();
        }

        return $this->identifier;
    }

    public static function succeeded($type, $model, $identifier, DataSyncConnection $connection, $payload, $response) {
        return self::create([
            'successful'    => true,
            'action'        => $type,
            'model'         => $model,
            'identifier'    => $identifier,
            'connection'    => $connection->getName(),
            'payload'       => self::payloadShouldBeLogged() ? json_encode($payload) : null,
            'response'      => app()->environment('production') ? $response->getStatusCode() : $response->getBody(),
        ]);
    }

    public static function failed($type, $model, $identifier, DataSyncConnection $connection, $payload, $response) {
        return self::create([
            'successful'    => false,
            'action'        => $type,
            'model'         => $model,
            'identifier'    => $identifier,
            'connection'    => $connection->getName(),
            'payload'       => app()->environment('production') ? encrypt(json_encode($payload)) : json_encode($payload),
            'response'      => $response->getBody(),
        ]);
    }

    public static function payloadShouldBeLogged() {
        if (!app()->environment('production')) {
            return true;
        }

        return config('datasync.settings.log_payload_on_success', false);
    }
}

<?php

namespace Baufragen\DataSync;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Illuminate\Database\Eloquent\Model;

class DataSyncLog extends Model
{
    protected $guarded = [];

    public function scopeSuccessful($query) {
        $query->where('successful', true);
    }

    public function scopeFailed($query) {
        $query->where('successful', false);
    }

    public static function succeeded($type, $model, $identifier, DataSyncConnection $connection, $payload, $response) {
        return self::create([
            'successful'    => true,
            'action'        => $type,
            'model'         => $model,
            'identifier'    => $identifier,
            'connection'    => $connection->getName(),
            'payload'       => app()->environment('production') ? null : json_encode($payload),
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
}

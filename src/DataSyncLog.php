<?php

namespace Baufragen\DataSync;

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

    public static function succeeded($action, $model, $identifier, $connection, $payload, $response) {
        return self::create([
            'successful'    => true,
            'action'        => $action,
            'model'         => $model,
            'identifier'    => $identifier,
            'connection'    => $connection,
            'payload'       => app()->environment('production') ? null : json_encode($payload),
            'response'      => app()->environment('production') ? $response->getStatusCode() : $response->getBody(),
        ]);
    }

    public static function failed($action, $model, $identifier, $connection, $payload, $response) {
        return self::create([
            'successful'    => false,
            'action'        => $action,
            'model'         => $model,
            'identifier'    => $identifier,
            'connection'    => $connection,
            'payload'       => app()->environment('production') ? encrypt(json_encode($payload)) : json_encode($payload),
            'response'      => $response->getBody(),
        ]);
    }
}

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

    public static function succeeded($action, $model, $identifier, $connection) {
        return self::create([
            'successful'    => true,
            'action'        => $action,
            'model'         => $model,
            'identifier'    => $identifier,
            'connection'    => $connection,
        ]);
    }

    public static function failed($action, $model, $identifier, $connection, $payload, $response) {
        return self::create([
            'successful'    => false,
            'action'        => $action,
            'model'         => $model,
            'identifier'    => $identifier,
            'connection'    => $connection,
            'payload'       => encrypt(json_encode($payload)),
            'response'      => $response->getReasonPhrase(),
        ]);
    }
}

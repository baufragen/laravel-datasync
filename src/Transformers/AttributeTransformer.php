<?php

namespace Baufragen\DataSync\Transformers;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Illuminate\Http\Request;

class AttributeTransformer extends BaseTransformer {
    protected $attributes;

    public function __construct(Request $request, DataSyncConnection $connection)
    {
        parent::__construct($request, $connection);

        $this->attributes = $this->getAttributesFromRequest($request);
    }

    protected function validationRules() {
        return [
            'attributes'        => 'required|array',
            'attributes.id'    => 'required|integer',
        ];
    }

    public function sync() {
        if (method_exists($this->model, "beforeDataSyncExecution")) {
            if (!$this->model->beforeDataSyncExecution($this)) {
                return;
            }
        }

        $this->model->executeAttributeDataSync($this);

        if (method_exists($this->model, "afterDataSyncExecution")) {
            if (!$this->model->afterDataSyncExecution($this)) {
                return;
            }
        }
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function hasAttribute($key) {
        return isset($this->attributes[$key]);
    }

    public function pullAttribute($key) {
        $value = $this->attributes[$key];

        unset($this->attributes[$key]);

        return $value;
    }

    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
    }

    protected function getAttributesFromRequest(Request $request) {
        return collect($request->get('attributes', []))
            ->when($this->connection->isEncrypted(), function ($attributes) {
                return $attributes->map(function ($attribute) {
                    return decrypt($attribute);
                });
            })
            ->map(function ($value) {
                if (strpos($value, "bool:") === 0) {
                    return (bool)substr($value, 5);
                }

                return $value;
            });
    }

    public function getType() {
        return 'attribute';
    }
}

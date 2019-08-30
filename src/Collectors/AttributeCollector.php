<?php

namespace Baufragen\DataSync\Collectors;
use Baufragen\DataSync\Helpers\DataSyncAction;
use Baufragen\DataSync\Helpers\DataSyncConnection;
use Baufragen\DataSync\Interfaces\DataSyncing;

abstract class AttributeCollector extends BaseCollector implements DataSyncCollecting {
    protected $action;
    protected $attributes;

    public function __construct(DataSyncing $model) {
        parent::__construct();

        $this->attributes   = collect([]);

        $this->setModel($model);
        $this->identifier($model->id);
    }

    public function addAttribute($name, $value) {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    abstract protected function getSyncedAttributes();

    public function transform(DataSyncConnection $connection) {
        $this->attributes = $this->getSyncedAttributes();
        $this->model->beforeDataSyncAttributes($this);

        return $this->attributes
            ->map(function ($value) {
                if (is_bool($value)) {
                    return "bool:" . (string)$value;
                }

                return $value;
            })
            ->when($connection->isEncrypted(), function ($attributes) {
                return $attributes->mapWithKeys(function ($attribute, $key) {
                    return [$key => encrypt($attribute)];
                });
            })
            ->map(function ($value, $key) {
                return [
                    'name' => 'attributes[' . $key . ']',
                    'contents' => $value,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getType() {
        return 'attribute';
    }

    public function afterCreation(DataSyncAction $action = null) {
        $this->action = $action;
    }
}

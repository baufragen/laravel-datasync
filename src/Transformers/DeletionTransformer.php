<?php

namespace Baufragen\DataSync\Transformers;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Illuminate\Http\Request;

class DeletionTransformer extends BaseTransformer {
    public function __construct(Request $request, DataSyncConnection $connection)
    {
        parent::__construct($request, $connection);
    }

    public function sync() {
        if (method_exists($this->model, "beforeDataSyncDeletion")) {
            if (!$this->model->beforeDataSyncDeletion($this)) {
                return;
            }
        }

        $this->model->executeDataSyncDeletion();

        if (method_exists($this->model, "afterDataSyncDeletion")) {
            if (!$this->model->afterDataSyncDeletion($this)) {
                return;
            }
        }
    }

    public function getType() {
        return 'deletion';
    }
}

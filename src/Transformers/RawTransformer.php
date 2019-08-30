<?php

namespace Baufragen\DataSync\Transformers;

use Baufragen\DataSync\Helpers\DataSyncConnection;
use Illuminate\Http\Request;

class RawTransformer extends BaseTransformer {
    protected $rawData;

    public function __construct(Request $request, DataSyncConnection $connection)
    {
        parent::__construct($request, $connection);

        $this->rawData = $this->getRawDataFromRequest($request);
    }

    public function sync() {
        if (method_exists($this->model, "beforeDataSyncExecution")) {
            if (!$this->model->beforeDataSyncExecution($this)) {
                return;
            }
        }

        $this->model->executeRawDataSync($this);

        if (method_exists($this->model, "afterDataSyncExecution")) {
            if (!$this->model->afterDataSyncExecution($this)) {
                return;
            }
        }
    }

    public function getRawData() {
        return $this->rawData;
    }

    protected function getRawDataFromRequest(Request $request) {
        $decoded = json_decode($request->get('rawData'), true);

        return $this->connection->isEncrypted() ? decrypt($decoded) : $decoded;
    }

    protected function validationRules() {
        return [
            'rawdata' => 'required',
        ];
    }
}

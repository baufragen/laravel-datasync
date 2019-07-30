<?php

namespace Baufragen\DataSync\Controllers;

use Baufragen\DataSync\Exceptions\ConfigNotFoundException;
use Baufragen\DataSync\Helpers\DataSyncAction;
use Baufragen\DataSync\Rules\CorrectDataSyncApiKey;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class DataSyncController extends BaseController {
    use ValidatesRequests;

    public function handleIncomingSync(Request $request) {
        $this->validate($request, [
            'connection'    => 'required',
            'apikey'        =>  ['required', new CorrectDataSyncApiKey($request->get('connection'))],
            'model'         => 'required',
            'identifier'    => 'nullable|integer',
            'data'          => 'nullable',
            'action'        => 'required'
        ]);

        $connectionConfig = config('datasync.connections.' . $request->get('connection'));

        if (empty($connectionConfig)) {
            throw new ConfigNotFoundException('No config for incoming connection ' . $request->get('connection'));
        }

        $modelClass = app('dataSync.container')->getClassBySyncName($request->get('model'));

        $action = new DataSyncAction($request->get('action'));

        if ($action->isUpdate() || $action->isDelete()) {
            $model = $modelClass::findOrFail($request->get('identifier'));
        } else {
            $model = new $modelClass();
        }

        if (!method_exists($model, "dataSyncValidationRules")) {
            abort(500, "Class " . $modelClass . " does not implement HasDataSync trait");
        }

        $data = !empty($connectionConfig['encrypted']) ? decrypt($request->get('data')) : $request->get('data');

        if (!empty($data)) {
            Validator::make($data, $model->dataSyncValidationRules())
                ->validate();
        }

        try {
            $model->handleDataSync($action, $data);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response(null, 200);
    }

}

<?php

namespace Baufragen\DataSync\Controllers;

use Baufragen\DataSync\Helpers\DataSyncAction;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class DataSyncController extends BaseController {
    use ValidatesRequests;

    public function handleIncomingSync(Request $request) {
        $this->validate($request, [
            'model'         => 'required',
            'identifier'    => 'nullable|integer',
            'data'          => 'nullable',
            'action'        => 'required'
        ]);

        $modelClass = app('dataSync.container')->getClassBySyncName($request);

        $action = new DataSyncAction($request->get('action'));

        if ($action->isUpdate() || $action->isDelete()) {
            $model = $modelClass::findOrFail($request->get('identifier'));
        } else {
            $model = new $modelClass();
        }

        $validator = Validator::make($request->get('data'), $model->dataSyncValidationRules())
                                ->validate();

        if (!$model->handleDataSync($action, $request->get('data'))) {
            abort(500);
        }

        return response(null, 200);
    }

}

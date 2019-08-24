<?php

namespace Baufragen\DataSync\Controllers;

use Baufragen\DataSync\Exceptions\ConfigNotFoundException;
use Baufragen\DataSync\Helpers\DataSyncAction;
use Baufragen\DataSync\Helpers\DataSyncTransformer;
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
            'action'        => 'required',
            'relationdata'  => 'nullable',
            'files'         => 'nullable',
            'files.*'       => 'file',
            'customactions' => 'nullable',
        ]);

        try {

            $transformer = new DataSyncTransformer($request);
            $transformer->executeDataSync();

        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response(null, 200);
    }

}

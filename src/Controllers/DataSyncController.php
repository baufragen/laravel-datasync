<?php

namespace Baufragen\DataSync\Controllers;

use Baufragen\DataSync\Rules\CorrectDataSyncApiKey;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller as BaseController;

class DataSyncController extends BaseController {
    use ValidatesRequests;

    public function handleIncomingSync(Request $request) {
        $this->validate($request, [
            'connection'    => 'required',
            'apikey'        =>  ['required', new CorrectDataSyncApiKey($request->get('connection'))],
            'encrypted'     => 'required',
            'model'         => 'required',
            'identifier'    => 'nullable|integer',
            'type'          => ['required', 'string', Rule::in(array_keys(config('datasync.transformers')))],
        ]);

        try {

            $transformer = app('dataSync.handler')->getTransformerForType($request->get('type'), $request);
            $transformer->validate();
            $transformer->sync();

        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response(null, 200);
    }

}

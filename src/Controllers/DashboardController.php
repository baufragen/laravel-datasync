<?php

namespace Baufragen\DataSync\Controllers;

use Baufragen\DataSync\DataSyncLog;
use Baufragen\DataSync\Middleware\AuthenticateDashboard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DashboardController extends Controller {

    public function __construct() {
        $this->middleware(AuthenticateDashboard::class);
    }

    public function view(Request $request) {
        $logs = DataSyncLog::orderBy('created_at', 'DESC')
            ->when($request->filled('filter'), function ($query) use ($request) {
                switch ($request->get('filter')) {
                    case 'successful':
                        $query->successful();
                        break;

                    case 'failed':
                        $query->failed();
                        break;
                }
            })
            ->paginate(50);

        return view('dataSync::dashboard', compact("logs"));
    }

    public function details(DataSyncLog $dataSyncLog) {

        return view('dataSync::details', compact("dataSyncLog"));
    }

}

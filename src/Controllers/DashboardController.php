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
        $filter = $request->input('filter', [
            'success' => ['successful', 'failed'],
        ]);

        $logs = DataSyncLog::orderBy('created_at', 'DESC')
            ->when(!empty($filter['success']), function ($query) use ($filter) {
                $query->where(function($query) use ($filter) {
                    if (!empty($filter['success']) && in_array('successful', $filter['success'])) {
                        $query->where(function ($query) use ($filter) {
                            $query->successful();
                        });
                    }

                    if (!empty($filter['success']) && in_array('failed', $filter['success'])) {
                        $query->orWhere(function ($query) use ($filter) {
                            $query->failed();
                        });
                    }
                });
            })
            ->paginate(50);

        return view('dataSync::dashboard', compact("logs", "filter"));
    }

    public function details(DataSyncLog $log) {
        return view('dataSync::details', compact("log"));
    }

}

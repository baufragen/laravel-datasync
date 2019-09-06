<?php

namespace Baufragen\DataSync\Controllers;

use Baufragen\DataSync\Middleware\AuthenticateDashboard;
use Illuminate\Routing\Controller;

class DashboardController extends Controller {

    public function __construct() {
        $this->middleware(AuthenticateDashboard::class);
    }

    public function view() {
        return view('dataSync::dashboard');
    }

}

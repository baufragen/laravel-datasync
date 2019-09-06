<?php

namespace Baufragen\DataSync\Controllers;

use Illuminate\Routing\Controller;

class DashboardController extends Controller {

    public function view() {
        return view('dataSync::dashboard');
    }

}

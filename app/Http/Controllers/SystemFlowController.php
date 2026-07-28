<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SystemFlowController extends Controller
{
    public function index(): View
    {
        return view('system-flow.index');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ApiSystemController extends Controller
{
    public function documentation()
    {
        return view('admin.system.api-docs');
    }

    public function tester()
    {
        return view('admin.system.api-tester');
    }

    public function goldenGuide()
    {
        return view('admin.system.golden-guide');
    }
}

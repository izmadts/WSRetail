<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * The in-app Guide Book / SOP manual - one static reference page covering
 * every module (purpose, fields, step-by-step SOP, ledger impact, common
 * mistakes) for admin/manager/accountant users. Same "pure static content,
 * no dynamic data" shape as ApiSystemController::goldenGuide(), just not
 * admin-only since this audience is everyone who can reach /admin at all.
 */
class GuideController extends Controller
{
    public function index()
    {
        return view('admin.guide.index');
    }
}

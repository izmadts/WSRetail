<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        $payslips = $employee
            ? Payslip::with('payrollRun', 'payments')
                ->where('employee_id', $employee->id)
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('agent.payslips.index', compact('employee', 'payslips'));
    }
}

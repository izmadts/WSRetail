<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use Illuminate\Support\Facades\Auth;

/**
 * Self-service payslip viewing for anyone logged into the admin panel -
 * same "own Employee record only, no permission gate" shape as MyLeaveController.
 */
class MyPayslipController extends Controller
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

        return view('admin.payroll.my', compact('employee', 'payslips'));
    }
}

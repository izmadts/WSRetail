<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\SalaryComponent;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function index()
    {
        $payrollRuns = PayrollRun::withCount('payslips')->orderByDesc('year')->orderByDesc('month')->get();
        return view('admin.payroll.index', compact('payrollRuns'));
    }

    /**
     * Preview screen for a not-yet-run month - shows every active employee
     * with an active salary structure, their computed basic/allowances/
     * deductions, and an editable overtime input, before anything is
     * actually posted.
     */
    public function create(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $alreadyRun = PayrollRun::where('year', $year)->where('month', $month)->first();
        if ($alreadyRun) {
            return redirect()->route('admin.payroll-runs.show', $alreadyRun)
                ->with('error', "Payroll for {$alreadyRun->month_name} {$year} has already been processed.");
        }

        $periodStart = \Carbon\Carbon::create($year, $month, 1);

        $preview = Employee::where('is_active', true)
            ->whereIn('employment_status', ['active', 'on_leave'])
            ->orderBy('name')
            ->get()
            ->map(function ($employee) use ($periodStart) {
                $component = SalaryComponent::activeFor($employee, $periodStart);
                return [
                    'employee' => $employee,
                    'component' => $component,
                ];
            })
            ->filter(fn($row) => $row['component'] !== null)
            ->values();

        return view('admin.payroll.create', compact('year', 'month', 'preview'));
    }

    public function store(Request $request, PayrollService $payrollService)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'overtime' => 'nullable|array',
            'overtime.*' => 'nullable|numeric|min:0',
        ]);

        try {
            $payrollRun = $payrollService->runPayroll(
                $validated['year'],
                $validated['month'],
                $validated['overtime'] ?? [],
                Auth::id()
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.payroll-runs.show', $payrollRun)->with('success', 'Payroll processed successfully.');
    }

    public function show(PayrollRun $payrollRun)
    {
        $payslips = $payrollRun->payslips()->with('employee', 'payments')->orderBy('id')->get();
        return view('admin.payroll.show', compact('payrollRun', 'payslips'));
    }

    public function payPayslip(Request $request, Payslip $payslip, PayrollService $payrollService)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . max(0.01, $payslip->net_pay - $payslip->payments()->sum('amount')),
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,credit_card',
            'reference_no' => 'nullable|string|max:255',
        ]);

        try {
            $payrollService->payPayslip(
                $payslip,
                $validated['amount'],
                $validated['payment_date'],
                $validated['payment_method'],
                $validated['reference_no'] ?? null,
                Auth::id()
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Salary payment recorded.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Manages each employee's detailed, effective-dated pay structure. A raise
 * or change is a new row (see SalaryComponent), never an edit of an
 * existing one - so this controller only ever creates, never updates.
 */
class SalaryComponentController extends Controller
{
    public function index()
    {
        $employees = Employee::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($employee) {
                $employee->current_structure = SalaryComponent::activeFor($employee);
                return $employee;
            });

        return view('admin.salary-components.index', compact('employees'));
    }

    public function show(Employee $employee)
    {
        $history = SalaryComponent::where('employee_id', $employee->id)
            ->orderByDesc('effective_from')
            ->get();

        return view('admin.salary-components.show', compact('employee', 'history'));
    }

    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'basic_pay' => 'required|numeric|min:0',
            'house_rent_allowance' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'fuel_allowance' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'tax_deduction' => 'nullable|numeric|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
        ]);

        SalaryComponent::create([
            'employee_id' => $employee->id,
            'basic_pay' => $validated['basic_pay'],
            'house_rent_allowance' => $validated['house_rent_allowance'] ?? 0,
            'medical_allowance' => $validated['medical_allowance'] ?? 0,
            'fuel_allowance' => $validated['fuel_allowance'] ?? 0,
            'other_allowance' => $validated['other_allowance'] ?? 0,
            'tax_deduction' => $validated['tax_deduction'] ?? 0,
            'other_deduction' => $validated['other_deduction'] ?? 0,
            'effective_from' => $validated['effective_from'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.salary-components.show', $employee)->with('success', 'Salary structure saved.');
    }
}

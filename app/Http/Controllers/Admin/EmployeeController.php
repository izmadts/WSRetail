<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * HR employee records. Two populations land here:
 * - Auto-linked (user_id set) - created automatically the moment a
 *   matching User row is created, see Employee::createFromUser() /
 *   User::booted(). Not created through this controller at all.
 * - Standalone (user_id null) - added directly here for staff who never
 *   log into the software (operational/supply-chain roles etc.).
 */
class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('department', 'reportingManager')->orderBy('name')->get();
        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $managers = Employee::orderBy('name')->get();
        return view('admin.employees.create', compact('departments', 'managers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'cnic' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'department_id' => 'nullable|exists:departments,id',
            'designation' => 'nullable|string|max:255',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'date_of_joining' => 'nullable|date',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'admin_note' => 'nullable|string',
        ]);

        $validated['source'] = 'manually_added';
        $validated['employment_status'] = 'active';
        $validated['is_active'] = true;
        $validated['approved_at'] = now();
        $validated['approved_by'] = Auth::id();

        Employee::create($validated);

        return redirect()->route('admin.employees.index')->with('success', 'Employee added successfully!');
    }

    public function show(Employee $employee)
    {
        $employee->load('department', 'reportingManager', 'subordinates', 'user', 'approvedBy');
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $managers = Employee::where('id', '!=', $employee->id)->orderBy('name')->get();
        return view('admin.employees.edit', compact('employee', 'departments', 'managers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'cnic' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'department_id' => 'nullable|exists:departments,id',
            'designation' => 'nullable|string|max:255',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'date_of_joining' => 'nullable|date',
            'date_of_leaving' => 'nullable|date',
            'employment_status' => 'required|in:active,on_leave,suspended,terminated,resigned',
            'reporting_manager_id' => ['nullable', 'exists:employees,id', Rule::notIn([$employee->id])],
            'admin_note' => 'nullable|string',
        ]);

        $employee->update($validated);

        return redirect()->route('admin.employees.show', $employee)->with('success', 'Employee updated successfully!');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('admin.employees.index')->with('success', 'Employee record removed.');
    }

    /**
     * For a standalone employee (no login yet) - creates a real User
     * account and links it to this existing Employee record, rather than
     * letting User::booted()'s auto-create hook produce a second, separate
     * Employee row for the same person.
     */
    public function grantAccess(Request $request, Employee $employee)
    {
        if ($employee->user_id) {
            return back()->with('error', 'This employee already has system access.');
        }

        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'manager', 'accountant'])],
        ]);

        $user = User::create([
            'name' => $employee->name,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $employee->phone,
            'cnic' => $employee->cnic,
            'address' => $employee->address,
            'employee_id' => $employee->employee_code,
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // User::booted()'s created hook just auto-created a fresh, separate
        // Employee row for this new login - not wanted here, since
        // $employee already IS this person's HR record. Remove the
        // duplicate and link the original instead. forceDelete() (not
        // delete()) - Employee has SoftDeletes, and the user_id column has
        // a real DB-level unique constraint, which a soft-deleted row
        // still occupies; a plain delete() here would leave the duplicate
        // physically in place and the update below would collide with it.
        Employee::where('user_id', $user->id)->where('id', '!=', $employee->id)->forceDelete();
        $employee->update(['user_id' => $user->id, 'source' => 'auto_software_user']);

        return back()->with('success', "System access granted - {$employee->name} can now log in with {$validated['email']}.");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->orderBy('name')->get();
        return view('admin.employees.departments', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        Department::create($validated);

        return back()->with('success', 'Department created successfully!');
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;

        $department->update($validated);

        return back()->with('success', 'Department updated successfully!');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete a department with employees assigned to it!');
        }

        $department->delete();

        return back()->with('success', 'Department deleted successfully!');
    }
}

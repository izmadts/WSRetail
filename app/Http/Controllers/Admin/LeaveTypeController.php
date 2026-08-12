<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::withCount('leaveRequests')->orderBy('name')->get();
        return view('admin.leave-types.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:leave_types',
            'description' => 'nullable|string',
            'default_days_per_year' => 'required|integer|min:0|max:365',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_paid'] = $validated['is_paid'] ?? true;
        $validated['is_active'] = $validated['is_active'] ?? true;

        LeaveType::create($validated);

        return back()->with('success', 'Leave type created successfully!');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('leave_types')->ignore($leaveType->id)],
            'description' => 'nullable|string',
            'default_days_per_year' => 'required|integer|min:0|max:365',
            'is_paid' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_paid'] = $validated['is_paid'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? false;

        $leaveType->update($validated);

        return back()->with('success', 'Leave type updated successfully!');
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->leaveRequests()->count() > 0) {
            return back()->with('error', 'Cannot delete a leave type that already has requests recorded against it!');
        }

        $leaveType->delete();

        return back()->with('success', 'Leave type deleted successfully!');
    }
}

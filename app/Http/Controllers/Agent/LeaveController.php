<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Self-service leave for Sale Agents - same underlying leave_requests table
 * and approval queue (Admin\LeaveRequestController) as the admin panel's
 * "My Leave", just the agent-portal-facing half of it.
 */
class LeaveController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('name')->get();
        $year = now()->year;

        $balances = $employee
            ? $leaveTypes->map(fn($type) => [
                'leave_type' => $type,
                'used' => $type->usedDaysFor($employee, $year),
                'remaining' => $type->remainingDaysFor($employee, $year),
            ])
            : collect();

        $myRequests = $employee
            ? LeaveRequest::with('leaveType', 'approvedBy')
                ->where('employee_id', $employee->id)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('agent.leave.index', compact('employee', 'leaveTypes', 'balances', 'myRequests'));
    }

    public function store(Request $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return back()->with('error', 'No employee record found for your account.');
        }

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'nullable|string|max:500',
        ]);

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted.');
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        $employee = Auth::user()->employee;

        if (!$employee || (int) $leaveRequest->employee_id !== (int) $employee->id) {
            abort(403);
        }

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return back()->with('success', 'Leave request cancelled.');
    }
}

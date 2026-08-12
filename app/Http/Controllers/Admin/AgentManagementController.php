<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Sale;
use App\Models\AgentCommissionLog;
use App\Models\AgentMonthlyTarget;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AgentManagementController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Display a listing of all agents
     */
    public function index()
    {
        $agents = User::where('role', 'sales_agent')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.agents.index', compact('agents'));
    }

    /**
     * Display pending agent registrations
     */
    public function pending()
    {
        $agents = User::where('role', 'sales_agent')
            ->where('is_active', false)
            ->whereNull('approved_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.agents.pending', compact('agents'));
    }

    /**
     * Show agent approval form
     */
    public function approve(User $user)
    {
        if ($user->role !== 'sales_agent') {
            return back()->with('error', 'User is not a sales agent!');
        }

        // The per-agent slabs captured below start as a copy of the
        // org-wide policy (Settings > Commission & Bonus Policy) so a new
        // agent's defaults never drift from what's actually configured.
        $defaultCashTiers = CommissionService::getSetting('commission.cash_tiers');

        return view('admin.agents.approve', compact('user', 'defaultCashTiers'));
    }

    /**
     * Process agent approval
     */
    public function doApprove(Request $request, User $user)
    {
        if ($user->role !== 'sales_agent') {
            return back()->with('error', 'User is not a sales agent!');
        }

        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'fuel_allowance' => 'required|numeric|min:0',
            'commission_rate_credit' => 'required|numeric|min:0|max:100',
            'admin_note' => 'nullable|string',
            // Which customer channel(s) this agent is allowed to work -
            // gates their own customer/product/sale APIs (see
            // Api\Agent\CustomerController/ProductController/SaleController).
            'channel' => 'required|in:wholesale,retail,both',
            // Slabs validation
            'slabs' => 'required|array|min:1',
            'slabs.*.from' => 'required|numeric|min:0',
            'slabs.*.to' => 'nullable|numeric|gt:slabs.*.from',
            'slabs.*.rate' => 'required|numeric|min:0|max:100',
        ]);

        // Store slabs as JSON
        $slabs = collect($validated['slabs'])->map(function ($slab) {
            return [
                'from' => (float) $slab['from'],
                'to' => $slab['to'] ? (float) $slab['to'] : null,
                'rate' => (float) $slab['rate'],
            ];
        })->toArray();

        $user->update([
            'basic_salary' => $validated['basic_salary'],
            'fuel_allowance' => $validated['fuel_allowance'],
            'commission_rate_credit' => $validated['commission_rate_credit'],
            'commission_slabs' => $slabs,
            'admin_note' => $validated['admin_note'] ?? $user->admin_note,
            'channel' => $validated['channel'],
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent approved successfully!');
    }

    /**
     * Show agent rejection form
     */
    public function reject(User $user)
    {
        if ($user->role !== 'sales_agent') {
            return back()->with('error', 'User is not a sales agent!');
        }

        return view('admin.agents.reject', compact('user'));
    }

    /**
     * Process agent rejection
     */
    public function doReject(Request $request, User $user)
    {
        if ($user->role !== 'sales_agent') {
            return back()->with('error', 'User is not a sales agent!');
        }

        $validated = $request->validate([
            'admin_note' => 'required|string|min:10',
        ]);

        $user->update([
            'admin_note' => $validated['admin_note'],
            'is_active' => false,
        ]);

        return redirect()->route('admin.agents.index')
            ->with('error', 'Agent application rejected!');
    }

    /**
     * Display agent details
     */
    public function view(User $user)
    {
        if ($user->role !== 'sales_agent') {
            return back()->with('error', 'User is not a sales agent!');
        }

        $user->load('sales', 'customers');

        // Get commission summary. paid_amount is summed across ALL logs
        // (not just ones flagged fully is_paid) so a partially-paid log's
        // progress is actually reflected here.
        $totalCommission = AgentCommissionLog::where('agent_id', $user->id)->sum('amount');
        $paidCommission = AgentCommissionLog::where('agent_id', $user->id)->sum('paid_amount');
        $dueCommission = $totalCommission - $paidCommission;

        // Get monthly targets
        $monthlyTargets = AgentMonthlyTarget::where('agent_id', $user->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('admin.agents.view', compact('user', 'totalCommission', 'paidCommission', 'dueCommission', 'monthlyTargets'));
    }

    /**
     * Show agent edit form
     */
    public function edit(User $user)
    {
        if ($user->role !== 'sales_agent') {
            return back()->with('error', 'User is not a sales agent!');
        }

        // Same org-wide policy the Approve Agent page uses, so "Load
        // Default" here reflects Settings > Commission & Bonus Policy too.
        $defaultCashTiers = CommissionService::getSetting('commission.cash_tiers');

        return view('admin.agents.edit', compact('user', 'defaultCashTiers'));
    }

    /**
     * Update agent details
     */
    public function update(Request $request, User $user)
    {
        if ($user->role !== 'sales_agent') {
            return back()->with('error', 'User is not a sales agent!');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:50',
            'cnic' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'basic_salary' => 'nullable|numeric|min:0',
            'fuel_allowance' => 'nullable|numeric|min:0',
            'commission_rate_cash' => 'nullable|numeric|min:0|max:100',
            'commission_rate_credit' => 'nullable|numeric|min:0|max:100',
            'admin_note' => 'nullable|string',
            'channel' => 'nullable|in:wholesale,retail,both',
            'is_active' => 'boolean',
            // Slabs validation - same shape as the Approve Agent form.
            'slabs' => 'nullable|array',
            'slabs.*.from' => 'required_with:slabs|numeric|min:0',
            'slabs.*.to' => 'nullable|numeric|gt:slabs.*.from',
            'slabs.*.rate' => 'required_with:slabs|numeric|min:0|max:100',
        ]);

        // A checkbox rule of 'boolean' only validates the key if present -
        // it doesn't default one in when absent, so an unchecked box (which
        // sends no key at all) used to silently fall back to the old value
        // and the admin's "deactivate agent" click would do nothing.
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        if (!empty($validated['slabs'])) {
            $validated['commission_slabs'] = collect($validated['slabs'])->map(fn ($s) => [
                'from' => (float) $s['from'],
                'to' => $s['to'] ? (float) $s['to'] : null,
                'rate' => (float) $s['rate'],
            ])->toArray();
        }
        unset($validated['slabs']);

        $user->update($validated);

        return redirect()->route('admin.agents.view', $user)
            ->with('success', 'Agent updated successfully!');
    }

    /**
     * Delete agent
     */
    public function destroy(User $user)
    {
        if ($user->role !== 'sales_agent') {
            return back()->with('error', 'User is not a sales agent!');
        }

        if ($user->sales()->count() > 0) {
            return back()->with('error', 'Cannot delete agent with sales records!');
        }

        $user->delete();

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent deleted successfully!');
    }

    /**
     * Toggle agent status
     */
    public function toggleStatus(User $user)
    {
        if ($user->role !== 'sales_agent') {
            return back()->with('error', 'User is not a sales agent!');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Agent {$status} successfully!");
    }

    /**
     * Pay agent commission
     */
    public function payCommission(Request $request, User $user)
    {
        if ($user->role !== 'sales_agent') {
            return back()->with('error', 'User is not a sales agent!');
        }

        $totalDue = AgentCommissionLog::where('agent_id', $user->id)->sum('amount')
            - AgentCommissionLog::where('agent_id', $user->id)->sum('paid_amount');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . max($totalDue, 0.01),
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        // Allocates across unpaid logs (oldest first), actually tracking
        // paid_amount per log instead of flipping the whole log to
        // is_paid=true regardless of how much was actually paid against it.
        $this->commissionService->payCommission(
            $user,
            $validated['amount'],
            $validated['payment_date'],
            $validated['payment_method'],
            $validated['reference_no'] ?? null,
            $validated['notes'] ?? null,
            Auth::id()
        );

        return redirect()->route('admin.agents.view', $user)
            ->with('success', 'Commission paid successfully!');
    }

    /**
     * Hold an agent's commission on a specific sale (fraud/dispute/
     * overdue-recovery hold per the commission policy's outstanding-
     * balances clause).
     */
    public function holdCommission(Request $request, User $user, Sale $sale)
    {
        if ($sale->agent_id !== $user->id) {
            abort(404);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:5',
        ]);

        $this->commissionService->holdCommission($sale, $validated['reason']);

        return back()->with('success', 'Commission held for this sale.');
    }

    public function releaseCommission(User $user, Sale $sale)
    {
        if ($sale->agent_id !== $user->id) {
            abort(404);
        }

        $this->commissionService->releaseCommission($sale);

        return back()->with('success', 'Commission released for this sale.');
    }

    /**
     * Close the current month and post target-achievement bonuses for
     * every active agent (100%/120%/150% tiers) - previously only ever
     * computed for on-screen display, never actually paid.
     */
    public function closeMonth(Request $request)
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2020|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $results = $this->commissionService->closeMonthTargetBonuses(
            $validated['year'] ?? null,
            $validated['month'] ?? null
        );

        $bonusCount = collect($results)->where('bonus', '>', 0)->count();

        return back()->with('success', "Month closed - target bonuses posted for {$bonusCount} agent(s).");
    }

    /**
     * Create agent manually (Admin)
     */
    public function create()
    {
        return view('admin.agents.create');
    }

    /**
     * Store agent manually (Admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:50',
            'cnic' => 'nullable|string|max:20|unique:users',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'basic_salary' => 'nullable|numeric|min:0',
            'fuel_allowance' => 'nullable|numeric|min:0',
            'commission_rate_cash' => 'nullable|numeric|min:0|max:100',
            'commission_rate_credit' => 'nullable|numeric|min:0|max:100',
            'admin_note' => 'nullable|string',
            'channel' => 'nullable|in:wholesale,retail,both',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['role'] = 'sales_agent';
        $validated['password'] = Hash::make($request->password);
        $validated['approved_at'] = now();
        $validated['approved_by'] = Auth::id();

        $user = User::create($validated);

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent created successfully!');
    }
}

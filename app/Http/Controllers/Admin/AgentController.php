<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::withCount('sales')->orderBy('name')->get();
        return view('admin.agents.index', compact('agents'));
    }

    public function create()
    {
        return view('admin.agents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|unique:agents',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:agents',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'cnic' => 'nullable|string|max:20',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_rate' => 'required|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;

        Agent::create($validated);

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent created successfully!');
    }

    public function show(Agent $agent)
    {
        $agent->load(['sales' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }, 'commissionPayments']);
        return view('admin.agents.show', compact('agent'));
    }

    public function edit(Agent $agent)
    {
        return view('admin.agents.edit', compact('agent'));
    }

    public function update(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', Rule::unique('agents')->ignore($agent->id)],
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('agents')->ignore($agent->id)],
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'cnic' => 'nullable|string|max:20',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_rate' => 'required|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;

        $agent->update($validated);

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent updated successfully!');
    }

    public function destroy(Agent $agent)
    {
        if ($agent->sales()->count() > 0) {
            return back()->with('error', 'Cannot delete agent with sales records!');
        }

        $agent->delete();
        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent deleted successfully!');
    }

    public function toggleStatus(Agent $agent)
    {
        $agent->is_active = !$agent->is_active;
        $agent->save();
        return back()->with('success', 'Agent status updated!');
    }
}

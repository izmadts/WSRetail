<?php

namespace App\Http\Controllers\Api\Agent;

use App\Models\AgentCommissionLog;
use App\Http\Resources\Api\CommissionLogResource;
use Illuminate\Http\Request;

class CommissionController extends ApiController
{
    public function index(Request $request)
    {
        $query = AgentCommissionLog::where('agent_id', $this->agent()->id)->with('sale');

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('reference_type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_paid', $request->status === 'paid');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $commissions = $query->orderByDesc('created_at')->paginate($request->input('per_page', 20));

        return $this->paginated($commissions, fn ($c) => new CommissionLogResource($c));
    }

    public function summary(Request $request)
    {
        $agent = $this->agent();

        // totalPaid sums paid_amount (not amount where is_paid=true) so a
        // partially-paid log's progress actually counts - same fix as the
        // web commissions page.
        $totalEarned = (float) AgentCommissionLog::where('agent_id', $agent->id)->sum('amount');
        $totalPaid = (float) AgentCommissionLog::where('agent_id', $agent->id)->sum('paid_amount');

        $monthEarned = (float) AgentCommissionLog::where('agent_id', $agent->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $byType = function (string $type) use ($agent) {
            return (float) AgentCommissionLog::where('agent_id', $agent->id)
                ->where('reference_type', $type)
                ->sum('amount');
        };

        return $this->success([
            'total_earned' => $totalEarned,
            'total_paid' => $totalPaid,
            'total_due' => $totalEarned - $totalPaid,
            'month_earned' => $monthEarned,
            'breakdown' => [
                'sale' => $byType('sale'),
                'new_customer_bonus' => $byType('new_customer_bonus'),
                'recovery_bonus' => $byType('recovery_bonus'),
                'target_bonus' => $byType('target_bonus'),
            ],
        ]);
    }
}

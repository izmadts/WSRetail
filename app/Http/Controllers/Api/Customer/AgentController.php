<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\User;

class AgentController extends ApiController
{
    /**
     * Public (no token, no integration key) - just names/phones of active,
     * approved sales agents, for the one-time picker shown before /connect.
     * Nothing sensitive here, and there's no customer token to gate it with
     * yet at this point in the flow.
     *
     * This whole customer API is a wholesale-only channel (see the pricing
     * note on Api\Customer\OrderController::resolveChannel), so only agents
     * allowed to work wholesale are offered here. Agents with no channel set
     * (every agent created before this field existed) are unrestricted and
     * still appear - nobody who could see this list before is silently
     * dropped from it.
     */
    public function index()
    {
        $agents = User::where('role', 'sales_agent')
            ->where('is_active', true)
            ->whereNotNull('approved_at')
            ->where(fn ($q) => $q->whereNull('channel')->orWhereIn('channel', ['wholesale', 'both']))
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return $this->success($agents);
    }
}

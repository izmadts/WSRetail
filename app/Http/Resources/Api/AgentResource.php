<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'employee_id' => $this->employee_id,
            'phone' => $this->phone,
            'cnic' => $this->cnic,
            'address' => $this->address,
            'city' => $this->city,
            'whatsapp_number' => $this->whatsapp_number,
            'personal_photo' => $this->personal_photo,
            // 'wholesale', 'retail', 'both', or null (unrestricted, same as
            // 'both' - see User::allowsPriceField()). Gates which customers/
            // products this agent's own APIs return.
            'channel' => $this->channel,
            'commission_rate_cash' => (float) $this->commission_rate_cash,
            'commission_rate_credit' => (float) $this->commission_rate_credit,
            'payout_account_type' => $this->payout_account_type,
            'payout_account_title' => $this->payout_account_title,
            'payout_account_number' => $this->payout_account_number,
            'payout_account_provider' => $this->payout_account_provider,
            'is_active' => (bool) $this->is_active,
            'approved_at' => optional($this->approved_at)->toIso8601String(),
            'last_login_at' => optional($this->last_login_at)->toIso8601String(),
        ];
    }
}

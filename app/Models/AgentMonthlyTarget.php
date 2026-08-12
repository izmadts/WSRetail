<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentMonthlyTarget extends Model
{
    protected $fillable = [
        'agent_id',
        'year',
        'month',
        'target_amount',
        'achieved_amount',
        'achievement_percentage',
        'bonus_earned',
        'is_paid'
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'achieved_amount' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
        'bonus_earned' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    // Relationships
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // Scopes
    public function scopeByAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    // Accessors
    public function getFormattedTargetAttribute()
    {
        return 'Rs. ' . number_format($this->target_amount, 2);
    }

    public function getFormattedAchievedAttribute()
    {
        return 'Rs. ' . number_format($this->achieved_amount, 2);
    }

    public function getFormattedBonusAttribute()
    {
        return 'Rs. ' . number_format($this->bonus_earned, 2);
    }

    public function getMonthNameAttribute()
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    // Helpers
    public function calculateAchievement()
    {
        if ($this->target_amount > 0) {
            $this->achievement_percentage = ($this->achieved_amount / $this->target_amount) * 100;
        } else {
            $this->achievement_percentage = 0;
        }
        $this->save();
        return $this->achievement_percentage;
    }

    /**
     * Reads tiers from the same admin-configurable setting
     * CommissionService::closeMonthTargetBonuses() actually pays from,
     * instead of a third hardcoded copy of the same tiers that could
     * silently drift from what Settings says and what actually gets paid.
     */
    public function calculateBonus()
    {
        $tiers = \App\Services\CommissionService::getSetting('commission.target_bonus_tiers');
        $bonus = 0;

        foreach ($tiers as $tier) {
            if ($this->achievement_percentage >= $tier['achievement_pct']) {
                $bonus = max($bonus, $tier['bonus']);
            }
        }

        $this->bonus_earned = $bonus;
        $this->save();
        return $this->bonus_earned;
    }
}
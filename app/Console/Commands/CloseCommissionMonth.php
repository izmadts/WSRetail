<?php

namespace App\Console\Commands;

use App\Services\CommissionService;
use Illuminate\Console\Command;

class CloseCommissionMonth extends Command
{
    protected $signature = 'commission:close-month {--year=} {--month=}';

    protected $description = 'Post monthly target-achievement bonuses (100%/120%/150% tiers) for every active sales agent';

    public function handle(CommissionService $commissionService)
    {
        $year = $this->option('year') ?: now()->year;
        $month = $this->option('month') ?: now()->month;

        $this->info("Closing commission month {$year}-{$month}...");

        $results = $commissionService->closeMonthTargetBonuses($year, $month);

        $this->table(
            ['Agent ID', 'Achievement %', 'Bonus'],
            collect($results)->map(fn ($r) => [$r['agent_id'], $r['achievement_pct'] . '%', 'Rs. ' . number_format($r['bonus'], 2)])
        );

        $bonusCount = collect($results)->where('bonus', '>', 0)->count();
        $this->info("Done. Target bonuses posted for {$bonusCount} agent(s).");

        return self::SUCCESS;
    }
}

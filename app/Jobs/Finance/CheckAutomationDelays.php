<?php

namespace App\Jobs\Finance;

use App\Models\AutomationDelay;
use App\Services\AutomationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Hourly: executes automation rules whose trigger_delay_hours have elapsed.
 */
class CheckAutomationDelays implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(AutomationService $automation): void
    {
        $due = AutomationDelay::withoutGlobalScopes()
            ->with('rule')
            ->where('run_at', '<=', now())
            ->get();

        foreach ($due as $delay) {
            $automation->runDelayed($delay);
        }
    }
}

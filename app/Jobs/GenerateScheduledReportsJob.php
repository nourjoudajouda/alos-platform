<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ClientReportSetting;
use App\Services\Reports\ReportOrchestrator;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ALOS-S1-15.6 — Generate reports for all clients with matching frequency (weekly/monthly).
 */
class GenerateScheduledReportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $frequency // 'weekly' | 'monthly'
    ) {}

    public function handle(ReportOrchestrator $orchestrator): void
    {
        $periodStart = match ($this->frequency) {
            'weekly' => Carbon::now()->subWeek()->startOfWeek(),
            'monthly' => Carbon::now()->subMonth()->startOfMonth(),
            default => Carbon::now()->subWeek()->startOfWeek(),
        };
        $periodEnd = match ($this->frequency) {
            'weekly' => Carbon::now()->subWeek()->endOfWeek(),
            'monthly' => Carbon::now()->subMonth()->endOfMonth(),
            default => Carbon::now()->subWeek()->endOfWeek(),
        };

        $settings = ClientReportSetting::query()
            ->where('frequency', $this->frequency)
            ->where(function ($q) {
                $q->where('case_status_enabled', true)
                    ->orWhere('activity_summary_enabled', true)
                    ->orWhere('new_documents_enabled', true);
            })
            ->with('client')
            ->get();

        foreach ($settings as $setting) {
            $client = $setting->client;
            if (! $client) {
                continue;
            }
            try {
                $reports = $orchestrator->generateAllForClient($client, $periodStart, $periodEnd);
                foreach ($reports as $report) {
                    DispatchReportDeliveryJob::dispatch($report);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\GeneratedReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ALOS-S1-15.6 / 15.8 — Dispatch delivery (in-app + email) for one generated report.
 */
class DispatchReportDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public GeneratedReport $report
    ) {}

    public function handle(): void
    {
        $report = $this->report->fresh();
        if (! $report || $report->status !== GeneratedReport::STATUS_GENERATED) {
            return;
        }

        $client = $report->client;
        $settings = $client->reportSettings;
        if (! $settings) {
            $report->markFailed();
            return;
        }

        try {
            if ($settings->shouldSendEmail()) {
                SendReportEmailJob::dispatch($report);
                $report->markSent();
            } else {
                $report->markSent();
            }
        } catch (\Throwable $e) {
            report($e);
            $report->markFailed();
        }
    }
}

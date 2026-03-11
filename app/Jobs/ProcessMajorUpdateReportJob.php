<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ClientReportSetting;
use App\Services\Reports\NewDocumentsReportGenerator;
use App\Services\Reports\ReportOrchestrator;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ALOS-S1-15.9 — On major update: generate and send reports for client if settings allow.
 * Triggered when: case status change, new shared document, new/updated session, new consultation, etc.
 */
class ProcessMajorUpdateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $clientId,
        public string $triggerType // e.g. case_status_change, document_shared, session_added, consultation_added
    ) {}

    public function handle(ReportOrchestrator $orchestrator): void
    {
        $client = Client::with('reportSettings')->find($this->clientId);
        if (! $client) {
            return;
        }

        $settings = $client->reportSettings;
        if (! $settings || ! $settings->reportsEnabled()) {
            return;
        }
        if ($settings->frequency !== ClientReportSetting::FREQUENCY_MAJOR_UPDATE) {
            return;
        }

        $periodStart = now()->subDays(7);
        $periodEnd = now();

        $reports = $orchestrator->generateAllForClient($client, $periodStart, $periodEnd);
        foreach ($reports as $report) {
            DispatchReportDeliveryJob::dispatch($report);
        }
    }
}

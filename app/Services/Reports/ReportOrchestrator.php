<?php

namespace App\Services\Reports;

use App\Models\Client;
use App\Models\ClientReportSetting;
use App\Models\GeneratedReport;
use Carbon\Carbon;

/**
 * ALOS-S1-15 — Orchestrates report generation, storage, and duplicate check.
 */
class ReportOrchestrator
{
    public function __construct(
        protected CaseStatusReportGenerator $caseStatusGenerator,
        protected ActivitySummaryReportGenerator $activitySummaryGenerator,
        protected NewDocumentsReportGenerator $newDocumentsGenerator,
    ) {}

    /**
     * Generate and persist a single report type for a client. Returns the GeneratedReport or null if duplicate/skipped.
     */
    public function generateAndStore(
        Client $client,
        string $reportType,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null,
        bool $skipDuplicateCheck = false
    ): ?GeneratedReport {
        $periodStart = $periodStart ?? now()->startOfDay();
        $periodEnd = $periodEnd ?? now()->endOfDay();

        if (! $skipDuplicateCheck && $this->existsForPeriod($client->id, $reportType, $periodStart, $periodEnd)) {
            return null;
        }

        $payload = $this->buildPayload($client, $reportType, $periodStart, $periodEnd);
        if ($payload === null) {
            return null;
        }

        $report = GeneratedReport::create([
            'tenant_id' => $client->tenant_id,
            'client_id' => $client->id,
            'report_type' => $reportType,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'title' => $payload['title'],
            'payload_json' => $payload,
            'status' => GeneratedReport::STATUS_GENERATED,
            'generated_at' => now(),
        ]);

        return $report;
    }

    /**
     * Generate all report types enabled in settings for a client (e.g. for scheduled run).
     */
    public function generateAllForClient(Client $client, Carbon $periodStart, Carbon $periodEnd): array
    {
        $settings = $client->reportSettings;
        if (! $settings || ! $settings->reportsEnabled()) {
            return [];
        }

        $reports = [];
        if ($settings->case_status_enabled) {
            $r = $this->generateAndStore($client, GeneratedReport::TYPE_CASE_STATUS, $periodStart, $periodEnd);
            if ($r) {
                $reports[] = $r;
            }
        }
        if ($settings->activity_summary_enabled) {
            $r = $this->generateAndStore($client, GeneratedReport::TYPE_ACTIVITY_SUMMARY, $periodStart, $periodEnd);
            if ($r) {
                $reports[] = $r;
            }
        }
        if ($settings->new_documents_enabled) {
            $r = $this->generateAndStore($client, GeneratedReport::TYPE_NEW_DOCUMENTS, $periodStart, $periodEnd);
            if ($r) {
                $reports[] = $r;
            }
        }

        return $reports;
    }

    public function existsForPeriod(int $clientId, string $reportType, Carbon $periodStart, Carbon $periodEnd): bool
    {
        return GeneratedReport::query()
            ->where('client_id', $clientId)
            ->where('report_type', $reportType)
            ->where('period_start', $periodStart->format('Y-m-d'))
            ->where('period_end', $periodEnd->format('Y-m-d'))
            ->exists();
    }

    protected function buildPayload(Client $client, string $reportType, Carbon $periodStart, Carbon $periodEnd): ?array
    {
        return match ($reportType) {
            GeneratedReport::TYPE_CASE_STATUS => $this->caseStatusGenerator->generate($client, $periodStart, $periodEnd),
            GeneratedReport::TYPE_ACTIVITY_SUMMARY => $this->activitySummaryGenerator->generate($client, $periodStart, $periodEnd),
            GeneratedReport::TYPE_NEW_DOCUMENTS => $this->newDocumentsGenerator->generate($client, $periodStart, $periodEnd),
            default => null,
        };
    }
}

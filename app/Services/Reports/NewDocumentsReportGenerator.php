<?php

namespace App\Services\Reports;

use App\Models\Client;
use Carbon\Carbon;

/**
 * ALOS-S1-15.5 — New Documents Report Generator.
 * Documents newly shared with the client in a period; optional link to case/consultation.
 */
class NewDocumentsReportGenerator
{
    public function generate(Client $client, Carbon $periodStart, Carbon $periodEnd): array
    {
        $periodEndEod = $periodEnd->copy()->endOfDay();

        $documents = $client->documents()
            ->where('visibility', 'shared')
            ->whereBetween('created_at', [$periodStart, $periodEndEod])
            ->with(['case', 'consultation'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($d) => [
                'document_id' => $d->id,
                'name' => $d->name,
                'description' => $d->description,
                'shared_at' => $d->created_at->toIso8601String(),
                'case_id' => $d->case_id,
                'case_number' => $d->case?->case_number,
                'consultation_id' => $d->consultation_id,
                'consultation_title' => $d->consultation?->title,
            ])
            ->values()
            ->all();

        $title = __('New Documents Report') . ' — ' . $client->name . ' — ' . $periodStart->format('Y-m-d') . ' / ' . $periodEnd->format('Y-m-d');

        return [
            'title' => $title,
            'report_type' => 'new_documents',
            'client_id' => $client->id,
            'client_name' => $client->name,
            'generated_at' => now()->toIso8601String(),
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'documents' => $documents,
            'summary' => [
                'total_documents' => count($documents),
            ],
        ];
    }

    /**
     * For major_update trigger: documents shared in the last N days (no fixed period).
     */
    public function generateForMajorUpdate(Client $client, int $days = 7): array
    {
        $periodEnd = now();
        $periodStart = now()->subDays($days);
        return $this->generate($client, $periodStart, $periodEnd);
    }
}

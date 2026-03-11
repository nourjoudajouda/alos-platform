<?php

namespace App\Services\Reports;

use App\Models\CaseModel;
use App\Models\Client;
use Carbon\Carbon;

/**
 * ALOS-S1-15.3 — Case Status Report Generator.
 * Lists client cases: number, type, current status, last update, responsible lawyer.
 */
class CaseStatusReportGenerator
{
    public function generate(Client $client, ?Carbon $periodStart = null, ?Carbon $periodEnd = null): array
    {
        $cases = $client->cases()
            ->with('responsibleLawyer')
            ->orderBy('case_number')
            ->get();

        $items = $cases->map(function ($case) {
            return [
                'case_id' => $case->id,
                'case_number' => $case->case_number,
                'case_type' => $case->case_type,
                'status' => $case->status,
                'status_label' => CaseModel::STATUSES[$case->status] ?? $case->status,
                'last_updated' => $case->updated_at?->toIso8601String(),
                'responsible_lawyer' => $case->responsibleLawyer ? [
                    'id' => $case->responsibleLawyer->id,
                    'name' => $case->responsibleLawyer->name,
                    'email' => $case->responsibleLawyer->email,
                ] : null,
            ];
        })->values()->all();

        $title = __('Case Status Report') . ' — ' . $client->name . ' — ' . now()->format('Y-m-d');

        return [
            'title' => $title,
            'report_type' => 'case_status',
            'client_id' => $client->id,
            'client_name' => $client->name,
            'generated_at' => now()->toIso8601String(),
            'period_start' => $periodStart?->format('Y-m-d'),
            'period_end' => $periodEnd?->format('Y-m-d'),
            'cases' => $items,
            'summary' => [
                'total_cases' => count($items),
                'by_status' => $cases->groupBy('status')->map->count()->all(),
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\GeneratedReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-15.7 — Client Portal: client sees only their own generated reports.
 */
class PortalReportController extends Controller
{
    private function getClient(\Illuminate\Contracts\Auth\Authenticatable $user): \App\Models\Client
    {
        $client = $user->client;
        if (! $client) {
            abort(404, __('Client not found.'));
        }
        return $client;
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $client = $this->getClient($user);

        $reports = $client->generatedReports()
            ->orderByDesc('generated_at')
            ->paginate(15)
            ->withQueryString();

        $reportTypes = [
            GeneratedReport::TYPE_CASE_STATUS => __('Case Status'),
            GeneratedReport::TYPE_ACTIVITY_SUMMARY => __('Activity Summary'),
            GeneratedReport::TYPE_NEW_DOCUMENTS => __('New Documents'),
        ];

        return view('portal.reports.index', [
            'client' => $client,
            'reports' => $reports,
            'reportTypes' => $reportTypes,
        ]);
    }

    public function show(Request $request, GeneratedReport $report): View
    {
        $user = $request->user();
        $client = $this->getClient($user);

        if ($report->client_id !== $client->id) {
            abort(404, __('Not found.'));
        }

        $payload = $report->getPayload();

        return view('portal.reports.show', [
            'client' => $client,
            'report' => $report,
            'payload' => $payload,
        ]);
    }
}

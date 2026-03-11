<?php

namespace App\Http\Controllers;

use App\Models\GeneratedReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-15.8 — Signed URL to view a generated report (for email link).
 */
class ReportViewController extends Controller
{
    public function show(Request $request, GeneratedReport $report): View
    {
        if (! $request->hasValidSignature()) {
            abort(403, __('Invalid or expired link.'));
        }

        $report->load('client');
        $payload = $report->getPayload();

        return view('reports.show-signed', [
            'report' => $report,
            'payload' => $payload,
        ]);
    }
}

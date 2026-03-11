<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Client;
use App\Models\ClientReportSetting;
use App\Models\GeneratedReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * ALOS-S1-15.7 — Reports tab on client: settings + list of generated reports (office).
 */
class ClientReportController extends Controller
{
    private function authorizeClient(Client $client): void
    {
        $user = auth()->user();
        if ($user instanceof Admin) {
            return;
        }
        if ($user->isClientPortalUser()) {
            abort(403, __('Access denied.'));
        }
        if (! $client->teamAccess()->where('user_id', $user->id)->exists()) {
            abort(404, __('Not found.'));
        }
    }

    public function index(Client $client): View
    {
        $this->authorizeClient($client);
        if (! auth()->user()->can('reports.view')) {
            abort(403, __('You do not have permission to view reports.'));
        }

        $settings = $client->reportSettings ?? new ClientReportSetting([
            'client_id' => $client->id,
            'tenant_id' => $client->tenant_id,
            'case_status_enabled' => true,
            'activity_summary_enabled' => true,
            'new_documents_enabled' => true,
            'delivery_channel' => ClientReportSetting::DELIVERY_BOTH,
            'frequency' => ClientReportSetting::FREQUENCY_WEEKLY,
            'send_to_client' => true,
            'send_to_responsible_lawyer' => true,
            'send_to_office_management' => false,
        ]);

        $reports = $client->generatedReports()->orderByDesc('generated_at')->paginate(15);

        return view('core::content.clients.reports.index', [
            'client' => $client,
            'settings' => $settings,
            'reports' => $reports,
        ]);
    }

    public function updateSettings(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($client);
        if (! auth()->user()->can('reports.manage')) {
            abort(403, __('You do not have permission to manage report settings.'));
        }

        $validated = $request->validate([
            'case_status_enabled' => ['boolean'],
            'activity_summary_enabled' => ['boolean'],
            'new_documents_enabled' => ['boolean'],
            'delivery_channel' => ['required', Rule::in([
                ClientReportSetting::DELIVERY_IN_APP,
                ClientReportSetting::DELIVERY_EMAIL,
                ClientReportSetting::DELIVERY_BOTH,
            ])],
            'frequency' => ['required', Rule::in([
                ClientReportSetting::FREQUENCY_WEEKLY,
                ClientReportSetting::FREQUENCY_MONTHLY,
                ClientReportSetting::FREQUENCY_MAJOR_UPDATE,
            ])],
            'send_to_client' => ['boolean'],
            'send_to_responsible_lawyer' => ['boolean'],
            'send_to_office_management' => ['boolean'],
        ]);

        $validated['case_status_enabled'] = $request->boolean('case_status_enabled');
        $validated['activity_summary_enabled'] = $request->boolean('activity_summary_enabled');
        $validated['new_documents_enabled'] = $request->boolean('new_documents_enabled');
        $validated['send_to_client'] = $request->boolean('send_to_client');
        $validated['send_to_responsible_lawyer'] = $request->boolean('send_to_responsible_lawyer');
        $validated['send_to_office_management'] = $request->boolean('send_to_office_management');
        $validated['tenant_id'] = $client->tenant_id;
        $validated['client_id'] = $client->id;

        ClientReportSetting::updateOrCreate(
            ['client_id' => $client->id],
            $validated
        );

        return redirect()
            ->route('admin.core.clients.reports.index', $client)
            ->with('success', __('Report settings saved.'));
    }

    public function show(Client $client, GeneratedReport $report): View
    {
        $this->authorizeClient($client);
        if (! auth()->user()->can('reports.view')) {
            abort(403, __('You do not have permission to view reports.'));
        }
        if ($report->client_id !== $client->id) {
            abort(404, __('Not found.'));
        }

        $payload = $report->getPayload();

        return view('core::content.clients.reports.show', [
            'client' => $client,
            'report' => $report,
            'payload' => $payload,
        ]);
    }
}

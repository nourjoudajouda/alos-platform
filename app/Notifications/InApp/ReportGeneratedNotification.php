<?php

namespace App\Notifications\InApp;

use App\Models\GeneratedReport;
use App\Services\InAppNotificationService;
use Illuminate\Support\Facades\App;

/**
 * ALOS-S1-26 — Report generated: notify client and/or responsible lawyer per settings.
 */
class ReportGeneratedNotification
{
    public static function send(GeneratedReport $report): void
    {
        $client = $report->client;
        $tenantId = $client->tenant_id;
        $title = __('Report ready');
        $messageBody = __('A new report has been generated for :client.', ['client' => $client->name]);
        $data = [
            'link' => route('admin.core.clients.reports.show', [$client, $report]),
            'entity_type' => 'report',
            'entity_id' => $report->id,
            'client_id' => $client->id,
        ];
        $service = App::make(InAppNotificationService::class);
        $settings = $client->reportSettings;

        if ($settings && $settings->send_to_client) {
            $portalUser = $client->portalUser;
            if ($portalUser) {
                $data['link'] = route('portal.reports.show', ['report' => $report]);
                $service->notify($portalUser->id, \App\Models\InAppNotification::TYPE_REPORT_GENERATED, $title, $messageBody, $tenantId, $data);
            }
        }
        if ($settings && $settings->send_to_responsible_lawyer) {
            $lead = $client->leadLawyer();
            if ($lead) {
                $data['link'] = route('admin.core.clients.reports.show', [$client, $report]);
                $service->notify($lead->id, \App\Models\InAppNotification::TYPE_REPORT_GENERATED, $title, $messageBody, $tenantId, $data);
            }
        }
        if ($settings && $settings->send_to_office_management) {
            foreach ($client->teamAccess()->pluck('id') as $uid) {
                $data['link'] = route('admin.core.clients.reports.show', [$client, $report]);
                $service->notify($uid, \App\Models\InAppNotification::TYPE_REPORT_GENERATED, $title, $messageBody, $tenantId, $data);
            }
        }
        if (! $settings) {
            $lead = $client->leadLawyer();
            if ($lead) {
                $service->notify($lead->id, \App\Models\InAppNotification::TYPE_REPORT_GENERATED, $title, $messageBody, $tenantId, $data);
            }
        }
    }
}

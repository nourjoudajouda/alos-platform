<?php

namespace App\Jobs;

use App\Mail\ClientReportMail;
use App\Models\GeneratedReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * ALOS-S1-15.8 — Send report notification by email to configured recipients.
 */
class SendReportEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public GeneratedReport $report
    ) {}

    public function handle(): void
    {
        $report = $this->report->fresh(['client.reportSettings', 'client.portalUser', 'client.teamAccess']);
        if (! $report) {
            return;
        }

        $settings = $report->client->reportSettings;
        if (! $settings || ! $settings->shouldSendEmail()) {
            return;
        }

        $recipients = [];

        if ($settings->send_to_client && $report->client->email) {
            $recipients[] = ['email' => $report->client->email, 'name' => $report->client->name];
        }
        if ($settings->send_to_responsible_lawyer) {
            $lead = $report->client->leadLawyer();
            if ($lead && $lead->email) {
                $recipients[] = ['email' => $lead->email, 'name' => $lead->name];
            }
        }
        if ($settings->send_to_office_management) {
            $tenant = $report->client->tenant;
            if ($tenant && $tenant->email) {
                $recipients[] = ['email' => $tenant->email, 'name' => $tenant->name . ' (Office)'];
            }
        }

        $recipients = collect($recipients)->unique('email')->values()->all();
        foreach ($recipients as $to) {
            try {
                Mail::to($to['email'], $to['name'])->send(new ClientReportMail($report));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}

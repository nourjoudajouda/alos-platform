<?php

namespace App\Mail;

use App\Models\GeneratedReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * ALOS-S1-15.8 — Email delivery for generated report (title, summary, link to view in ALOS).
 */
class ClientReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public GeneratedReport $report
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->report->title,
        );
    }

    public function content(): Content
    {
        $reportUrl = URL::temporarySignedRoute(
            'report.show.signed',
            now()->addDays(7),
            ['report' => $this->report->id]
        );

        return new Content(
            view: 'emails.client-report',
            with: ['reportUrl' => $reportUrl],
        );
    }
}

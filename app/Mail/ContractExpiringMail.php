<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * ALOS-S1-36 — Email when a tenant's contract is expiring soon.
 */
class ContractExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public int $daysUntilExpiry
    ) {}

    public function envelope(): Envelope
    {
        $appName = config('app.name');
        return new Envelope(
            subject: __('[:app] Your subscription expires in :days days', [
                'app' => $appName,
                'days' => $this->daysUntilExpiry,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-expiring',
        );
    }
}

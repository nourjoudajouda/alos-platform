<?php

namespace App\Jobs;

use App\Mail\ContractExpiringMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * ALOS-S1-36 — Send expiring-soon email to law firm admin(s) and optionally platform admin.
 */
class SendContractExpiringNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant
    ) {}

    public function handle(): void
    {
        $endDate = $this->tenant->contract_end_date;
        if (! $endDate) {
            return;
        }
        $daysUntilExpiry = (int) now()->startOfDay()->diffInDays($endDate->startOfDay(), false);
        if ($daysUntilExpiry < 0) {
            return;
        }
        $this->tenant->load('subscriptionPlan');

        $mailable = new ContractExpiringMail($this->tenant, $daysUntilExpiry);

        // Law firm admins (tenant staff with admin role)
        $admins = User::query()
            ->where('tenant_id', $this->tenant->id)
            ->whereNull('client_id')
            ->role('admin')
            ->whereNotNull('email')
            ->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send($mailable);
        }

        // If no admin found, send to first tenant staff with email
        if ($admins->isEmpty()) {
            $first = User::query()
                ->where('tenant_id', $this->tenant->id)
                ->whereNull('client_id')
                ->whereNotNull('email')
                ->first();
            if ($first) {
                Mail::to($first->email)->send($mailable);
            }
        }

        // Optional: notify platform admin (from config)
        $platformAdminEmail = config('mail.contract_expiry_notify_admin');
        if ($platformAdminEmail && filter_var($platformAdminEmail, FILTER_VALIDATE_EMAIL)) {
            Mail::to($platformAdminEmail)->send(new ContractExpiringMail($this->tenant, $daysUntilExpiry));
        }
    }
}

<?php

namespace App\Jobs;

use App\Services\SubscriptionMonitorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ALOS-S1-36 — Daily job: mark expired contracts and send expiring-soon notifications.
 */
class ProcessSubscriptionExpirationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SubscriptionMonitorService $monitor): void
    {
        // 1) Mark tenants as expired when contract_end_date has passed (cache invalidated inside)
        $monitor->markExpiredContracts();

        // 2) Dispatch expiring-soon notifications (7-day window)
        $expiringSoon = $monitor->getExpiringSoonTenants(SubscriptionMonitorService::DEFAULT_EXPIRING_SOON_DAYS);
        foreach ($expiringSoon as $tenant) {
            SendContractExpiringNotificationJob::dispatch($tenant);
        }
    }
}

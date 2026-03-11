<?php

namespace App\Services;

use App\Models\InAppNotification;
use App\Models\User;

/**
 * ALOS-S1-26 — Create in-app notifications (tenant-scoped, user-specific).
 */
class InAppNotificationService
{
    /**
     * Send notification to a single user. Tenant must match user's tenant (or null for admin).
     */
    public function notify(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?int $tenantId = null,
        ?array $data = null
    ): InAppNotification {
        $user = User::find($userId);
        $tid = $tenantId ?? ($user ? $user->tenant_id : null);

        return InAppNotification::create([
            'tenant_id' => $tid,
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'read_status' => false,
            'data' => $data ?? [],
        ]);
    }

    /**
     * Notify multiple users (e.g. client team).
     *
     * @param  array<int>  $userIds
     */
    public function notifyMany(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?int $tenantId = null,
        ?array $data = null
    ): void {
        $userIds = array_unique(array_filter($userIds));
        foreach ($userIds as $uid) {
            $this->notify($uid, $type, $title, $message, $tenantId, $data);
        }
    }
}

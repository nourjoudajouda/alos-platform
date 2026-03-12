<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\ComplianceLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * خدمة مركزية لـ Audit Log (حقول: action, old_values, new_values) و Compliance Log.
 */
class AuditLogService
{
    /**
     * تسجيل حدث في audit_logs بالحقول: action, entity_type, entity_id, old_values, new_values, ip_address.
     */
    public function recordAudit(
        string $action,
        string $entityType,
        $entityId,
        array $oldValues = [],
        array $newValues = [],
        ?int $tenantId = null,
        ?int $userId = null,
        ?int $adminUserId = null
    ): AuditLog {
        $user = $userId ? User::find($userId) : auth()->user();
        $uid = null;
        $aid = $adminUserId;
        if ($user instanceof User) {
            $uid = $userId ?? $user->id;
        } elseif ($user instanceof Admin && $aid === null) {
            $aid = $user->id;
        }
        $tenantId = $tenantId ?? ($user instanceof User ? $user->tenant_id : null);
        $entityId = $entityId ?? 0;

        return AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $uid,
            'admin_user_id' => $aid,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => (int) $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip() ?? '0.0.0.0',
        ]);
    }

    /**
     * Record platform-level audit (admin actions). Explicitly sets admin_user_id.
     */
    public function recordPlatformAudit(
        string $action,
        string $entityType,
        $entityId,
        array $oldValues = [],
        array $newValues = [],
        ?int $tenantId = null,
        ?int $adminUserId = null
    ): AuditLog {
        $admin = $adminUserId !== null ? Admin::find($adminUserId) : auth()->user();
        $aid = $admin instanceof Admin ? $admin->id : $adminUserId;

        return AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => null,
            'admin_user_id' => $aid,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => (int) ($entityId ?? 0),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip() ?? '0.0.0.0',
        ]);
    }

    public function recordCompliance(
        string $attemptedAction,
        string $description,
        ?string $targetEntity = null,
        ?int $targetId = null,
        ?int $tenantId = null,
        ?int $userId = null,
        ?string $userType = null,
        ?int $adminUserId = null
    ): ComplianceLog {
        $user = $userId ? User::find($userId) : auth()->user();
        $uid = ($user instanceof User) ? ($userId ?? $user->id) : null;
        $aid = $adminUserId;
        if ($user instanceof Admin && $aid === null) {
            $aid = $user->id;
        }
        if ($userType === null) {
            if ($user instanceof User) {
                $userType = $user->isClientPortalUser() ? 'client' : 'internal';
            } elseif ($user instanceof Admin) {
                $userType = 'admin';
            }
        }

        return ComplianceLog::create([
            'tenant_id' => $tenantId ?? ($user instanceof User ? $user->tenant_id : null),
            'user_id' => $uid,
            'admin_user_id' => $aid,
            'user_type' => $userType,
            'attempted_action' => $attemptedAction,
            'target_entity' => $targetEntity,
            'target_id' => $targetId,
            'description' => $description,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * تسجيل حدث مع تغيير (old_values / new_values).
     */
    public function recordAuditWithChanges(
        string $action,
        string $entityType,
        $entityId,
        ?array $oldValues,
        ?array $newValues,
        ?int $tenantId = null
    ): AuditLog {
        return $this->recordAudit(
            $action,
            $entityType,
            $entityId ?? 0,
            $oldValues ?? [],
            $newValues ?? [],
            $tenantId
        );
    }
}

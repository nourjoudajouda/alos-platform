<?php

namespace App\Services;

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
        ?int $userId = null
    ): AuditLog {
        $user = $userId ? User::find($userId) : auth()->user();
        $uid = null;
        if ($user instanceof User) {
            $uid = $userId ?? $user->id;
        }
        $tenantId = $tenantId ?? ($user instanceof User ? $user->tenant_id : null);
        $entityId = $entityId ?? 0;

        return AuditLog::create([
            'tenant_id' => $tenantId,
            'user_id' => $uid,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => (int) $entityId,
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
        ?string $userType = null
    ): ComplianceLog {
        $user = $userId ? User::find($userId) : auth()->user();
        $uid = ($user instanceof User) ? ($userId ?? $user->id) : null;
        if ($userType === null && $user instanceof User) {
            $userType = $user->isClientPortalUser() ? 'client' : 'internal';
        }

        return ComplianceLog::create([
            'tenant_id' => $tenantId ?? ($user instanceof User ? $user->tenant_id : null),
            'user_id' => $uid,
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

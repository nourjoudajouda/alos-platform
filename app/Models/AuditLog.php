<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit Log — حسب المخطط: tenant_id, user_id, action, entity_type, entity_id, old_values, new_values, ip_address, created_at.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_UPLOAD_DOCUMENT = 'upload_document';
    public const ACTION_SHARE_DOCUMENT = 'share_document';
    public const ACTION_SEND_MESSAGE = 'send_message';
    public const ACTION_CREATE_CASE = 'create_case';
    public const ACTION_UPDATE_CASE = 'update_case';
    public const ACTION_CREATE_CONSULTATION = 'create_consultation';
    public const ACTION_UPDATE_CONSULTATION = 'update_consultation';
    public const ACTION_UPDATE_SESSION = 'update_session';
    public const ACTION_CREATE_SESSION = 'create_session';
    public const ACTION_GENERATE_REPORT = 'generate_report';
    public const ACTION_CREATE_TENANT = 'create_tenant';
    public const ACTION_CREATE_USER = 'create_user';
    public const ACTION_CREATE_CLIENT = 'create_client';
    public const ACTION_UPDATE_CLIENT = 'update_client';

    public const ENTITY_TENANT = 'tenant';
    public const ENTITY_USER = 'user';
    public const ENTITY_CLIENT = 'client';
    public const ENTITY_CASE = 'case';
    public const ENTITY_CONSULTATION = 'consultation';
    public const ENTITY_DOCUMENT = 'document';
    public const ENTITY_MESSAGE_THREAD = 'message_thread';
    public const ENTITY_MESSAGE = 'message';
    public const ENTITY_CASE_SESSION = 'case_session';
    public const ENTITY_REPORT = 'report';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

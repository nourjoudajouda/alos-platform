<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ALOS-S1-37 — Platform Login Monitoring: admin login attempts.
 */
class AdminLoginLog extends Model
{
    public const UPDATED_AT = null;

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_LOGOUT = 'logout';

    protected $table = 'admin_login_logs';

    protected $fillable = [
        'admin_user_id',
        'login_time',
        'ip_address',
        'user_agent',
        'login_status',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'login_time' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_user_id');
    }
}

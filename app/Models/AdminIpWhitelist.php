<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ALOS-S1-37 — IP Restriction structure for admin access.
 * admin_user_id null = global whitelist rule; otherwise per-admin.
 */
class AdminIpWhitelist extends Model
{
    protected $table = 'admin_ip_whitelist';

    protected $fillable = [
        'admin_user_id',
        'ip_address',
        'status',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_user_id');
    }
}

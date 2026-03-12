<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * مدير النظام (SYSTEMADMIN) — مصادقة لوحة الإدارة (/admin).
 * منفصل عن جدول users (يوزرز التيننت والبوابة).
 * الحقول: id, name, email, password, role, created_at.
 */
class Admin extends Authenticatable
{
    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'two_factor_enabled',
        'two_factor_secret',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function loginLogs()
    {
        return $this->hasMany(AdminLoginLog::class, 'admin_user_id');
    }

    public function ipWhitelist()
    {
        return $this->hasMany(AdminIpWhitelist::class, 'admin_user_id');
    }

    /** Admins are never client portal users (they are platform administrators). */
    public function isClientPortalUser(): bool
    {
        return false;
    }

    /** Platform admins have full access; satisfy Spatie permission middleware without using HasRoles. */
    public function hasAnyPermission(...$permissions): bool
    {
        return true;
    }
}

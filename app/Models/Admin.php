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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * مدير النظام — مصادقة لوحة الإدارة (/admin).
 * منفصل عن جدول users (يوزرز التيننت والبوابة).
 */
class Admin extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Admins are never client portal users (they are platform administrators). */
    public function isClientPortalUser(): bool
    {
        return false;
    }
}

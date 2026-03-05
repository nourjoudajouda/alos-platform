<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    /** ALOS-S1-08 — Portal: view_only | messaging | messaging_upload */
    public const PORTAL_PERMISSION_VIEW_ONLY = 'view_only';
    public const PORTAL_PERMISSION_MESSAGING = 'messaging';
    public const PORTAL_PERMISSION_MESSAGING_UPLOAD = 'messaging_upload';

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'client_id',
        'portal_permission',
        'portal_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'portal_active' => 'boolean',
        ];
    }

    /**
     * Tenant this user belongs to (ALOS-S1-01).
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Client this user is linked to (ALOS-S1-08 — Client Portal). One-to-one; null for internal users.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Clients this user has access to (ALOS-S1-07 — Team Access). Internal users only.
     */
    public function clientAccess(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_access')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Whether this user is a client portal user (linked to a single client). */
    public function isClientPortalUser(): bool
    {
        return $this->client_id !== null;
    }

    /** Whether portal account is active and can log in. */
    public function isPortalActive(): bool
    {
        return $this->portal_active;
    }

    /**
     * Profile photo URL (Gravatar from email).
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        $hash = md5(strtolower(trim($this->email ?? '')));
        return "https://www.gravatar.com/avatar/{$hash}?s=128&d=identicon";
    }
}

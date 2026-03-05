<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * ALOS-S1-06 — Client Module Skeleton.
 * ALOS-S1-07 — Team Access: lead lawyer + assigned users via client_access.
 */
class Client extends Model
{
    public const TEAM_ROLE_LEAD_LAWYER = 'lead_lawyer';
    public const TEAM_ROLE_LAWYER = 'lawyer';
    public const TEAM_ROLE_ASSISTANT = 'assistant';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
    ];

    /**
     * Tenant (office) this client belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Team access: users who can view/manage this client (pivot role: lead_lawyer, lawyer, assistant).
     */
    public function teamAccess(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_access')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Lead lawyer for this client (single user with role lead_lawyer).
     */
    public function leadLawyer(): ?User
    {
        return $this->teamAccess()->wherePivot('role', self::TEAM_ROLE_LEAD_LAWYER)->first();
    }

    /**
     * Assigned users (lawyer + assistant), excluding lead (for display).
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->teamAccess()->wherePivot('role', '!=', self::TEAM_ROLE_LEAD_LAWYER);
    }

    /**
     * Portal user account for this client (ALOS-S1-08). One client can have at most one portal user.
     */
    public function portalUser(): HasOne
    {
        return $this->hasOne(User::class, 'client_id');
    }
}

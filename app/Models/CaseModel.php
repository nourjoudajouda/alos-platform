<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Case management: cases linked to clients; status open/pending/closed.
 * Visibility: user sees case only if they have client team access.
 */
class CaseModel extends Model
{
    protected $table = 'cases';

    public const STATUS_OPEN = 'open';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN => 'Open',
        self::STATUS_PENDING => 'Pending',
        self::STATUS_CLOSED => 'Closed',
    ];

    protected $fillable = [
        'tenant_id',
        'client_id',
        'case_number',
        'case_number_suffix',
        'case_type',
        'court_name',
        'responsible_lawyer_id',
        'status',
        'description',
    ];

    /**
     * Extract numeric suffix from full case number (e.g. "DE-001" -> "001", "XX-025" -> "025").
     */
    public static function parseCaseNumberSuffix(string $caseNumber): ?string
    {
        $caseNumber = trim($caseNumber);
        if ($caseNumber === '') {
            return null;
        }
        if (str_contains($caseNumber, '-')) {
            $parts = explode('-', $caseNumber);
            $suffix = trim(end($parts));
            return $suffix !== '' ? $suffix : null;
        }
        return $caseNumber;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function responsibleLawyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_lawyer_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'case_id');
    }

    /**
     * ALOS-S1-12 — Court sessions / hearings for this case.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(CaseSession::class, 'case_id');
    }

    /**
     * Suggested case number: first 2 letters of tenant name (uppercase) + "-" + zero-padded 3-digit next number.
     * Example: tenant "default" -> "DE-001", "DE-002", ...
     * If no tenant or empty name, uses "XX" as prefix.
     */
    public static function suggestCaseNumber(?int $tenantId, ?string $tenantName = null): string
    {
        $prefix = 'XX';
        if ($tenantName !== null && $tenantName !== '') {
            $lettersOnly = preg_replace('/[^a-zA-Z\p{Arabic}]/u', '', $tenantName);
            $two = mb_substr($lettersOnly, 0, 2);
            if (mb_strlen($two) >= 2) {
                $prefix = strtoupper(mb_substr($two, 0, 2));
            } elseif (mb_strlen($two) === 1) {
                $prefix = strtoupper($two . 'X');
            }
        }
        $next = static::query()->where('tenant_id', $tenantId)->count() + 1;
        return $prefix . '-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}

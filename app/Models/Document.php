<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * ALOS-S1-10 — Client Document Center. Internal (office only) or shared with client.
 */
class Document extends Model
{
    public const VISIBILITY_INTERNAL = 'internal';
    public const VISIBILITY_SHARED = 'shared';

    public const UPLOADED_BY_INTERNAL = 'internal';
    public const UPLOADED_BY_CLIENT = 'client';

    public const ALLOWED_MIMES = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    public const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    protected $fillable = [
        'tenant_id',
        'client_id',
        'case_id',
        'consultation_id',
        'uploaded_by',
        'uploaded_by_type',
        'name',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'visibility',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isInternal(): bool
    {
        return $this->visibility === self::VISIBILITY_INTERNAL;
    }

    public function isShared(): bool
    {
        return $this->visibility === self::VISIBILITY_SHARED;
    }

    public function uploadedByClient(): bool
    {
        return $this->uploaded_by_type === self::UPLOADED_BY_CLIENT;
    }

    public function getStorageDisk(): string
    {
        return 'local';
    }

    public function exists(): bool
    {
        return Storage::disk($this->getStorageDisk())->exists($this->file_path);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * ALOS-S1-09 — Attachment on a message.
 */
class MessageAttachment extends Model
{
    protected $fillable = [
        'message_id',
        'name',
        'path',
        'disk',
        'size',
        'mime_type',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function getStoragePath(): string
    {
        return $this->path;
    }

    public function getStream()
    {
        return Storage::disk($this->disk)->readStream($this->path);
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }
}

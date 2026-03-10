<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * ALOS-S1-21 — Tenant Branding Settings (Logo / Colors / Contact Info)
 */
class TenantSettings extends Model
{
    protected $fillable = [
        'tenant_id',
        'display_name',
        'logo_path',
        'favicon_path',
        'primary_color',
        'secondary_color',
        'email',
        'phone',
        'whatsapp',
        'address',
        'city',
        'short_description',
        'public_site_enabled',
    ];

    protected $casts = [
        'public_site_enabled' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** Get effective display name (settings or tenant name) */
    public function getDisplayName(): string
    {
        return $this->display_name ?: $this->tenant->name;
    }

    /** Get logo URL for display */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }
        return Storage::disk('public')->url($this->logo_path);
    }

    /** Upload logo and save path */
    public function uploadLogo(UploadedFile $file): string
    {
        if (!$file->isValid()) {
            throw new \RuntimeException(__('Invalid file upload.'));
        }

        $dir = 'tenant-logos/' . $this->tenant_id;
        $filename = $file->hashName();
        $fullPath = $dir . '/' . $filename;

        // Use put() with file contents to avoid getRealPath() issues on Windows
        $contents = $file->get();
        Storage::disk('public')->put($fullPath, $contents);

        $oldPath = $this->logo_path;
        $this->update(['logo_path' => $fullPath]);
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        return $fullPath;
    }

    /** Get favicon URL for display */
    public function getFaviconUrlAttribute(): ?string
    {
        if (!$this->favicon_path) {
            return null;
        }
        return Storage::disk('public')->url($this->favicon_path);
    }

    /** Upload favicon and save path */
    public function uploadFavicon(UploadedFile $file): string
    {
        if (!$file->isValid()) {
            throw new \RuntimeException(__('Invalid file upload.'));
        }
        $dir = 'tenant-favicons/' . $this->tenant_id;
        $filename = 'favicon.' . ($file->getClientOriginalExtension() ?: 'png');
        $fullPath = $dir . '/' . $filename;
        $contents = $file->get();
        Storage::disk('public')->put($fullPath, $contents);
        $oldPath = $this->favicon_path;
        $this->update(['favicon_path' => $fullPath]);
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        return $fullPath;
    }

    /** Remove favicon */
    public function removeFavicon(): void
    {
        if ($this->favicon_path && Storage::disk('public')->exists($this->favicon_path)) {
            Storage::disk('public')->delete($this->favicon_path);
        }
        $this->update(['favicon_path' => null]);
    }

    /** Delete logo file */
    public function removeLogo(): void
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            Storage::disk('public')->delete($this->logo_path);
        }
        $this->update(['logo_path' => null]);
    }

    /** Whether public site is enabled (from settings; overrides tenant if set) */
    public function hasPublicSiteEnabled(): bool
    {
        return (bool) ($this->public_site_enabled ?? true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ALOS-S1-30 — Global platform settings (key-value).
 * Not tenant-specific; only platform admins can modify.
 */
class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group_name',
    ];

    public const TYPE_STRING = 'string';
    public const TYPE_JSON = 'json';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_INTEGER = 'integer';

    public const GROUP_GENERAL = 'general';
    public const GROUP_MAIL = 'mail';
    public const GROUP_NOTIFICATIONS = 'notifications';
    public const GROUP_STORAGE = 'storage';
    public const GROUP_REGISTRATION = 'registration';
    public const GROUP_BRANDING = 'branding';

    /**
     * Keys that hold sensitive data (e.g. passwords) — never expose raw value in UI.
     */
    public const SENSITIVE_KEYS = [
        'mail_password',
    ];

    public function isSensitive(): bool
    {
        return in_array($this->key, self::SENSITIVE_KEYS, true);
    }

    /**
     * Cast value for reading based on type.
     */
    public function getTypedValueAttribute(): mixed
    {
        $v = $this->value;
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                return filter_var($v, FILTER_VALIDATE_BOOLEAN);
            case self::TYPE_INTEGER:
                return (int) $v;
            case self::TYPE_JSON:
                return is_string($v) ? json_decode($v, true) : $v;
            default:
                return $v;
        }
    }
}

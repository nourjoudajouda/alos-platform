<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

/**
 * ALOS-S1-30 — System Settings & Global Configuration.
 * Read/write platform-level settings with caching. Only platform admins may modify.
 */
class SystemSettingsService
{
    private const CACHE_KEY = 'system_settings';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get a single setting value by key. Returns default if not set.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->getAllCached();
        if (! isset($all[$key])) {
            return $default;
        }
        $item = $all[$key];
        return $this->castValue($item['value'], $item['type']);
    }

    /**
     * Get all settings for a group as key => typed value.
     */
    public function getGroup(string $groupName): array
    {
        $all = $this->getAllCached();
        $result = [];
        foreach ($all as $key => $item) {
            if (($item['group_name'] ?? '') === $groupName) {
                $result[$key] = $this->castValue($item['value'], $item['type']);
            }
        }
        return $result;
    }

    /**
     * Set a single setting. Creates or updates. Refreshes cache.
     */
    public function set(string $key, mixed $value, string $type = 'string', string $groupName = 'general'): void
    {
        $serialized = $this->serializeValue($value, $type);
        SystemSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $serialized,
                'type' => $type,
                'group_name' => $groupName,
            ]
        );
        $this->refreshCache();
    }

    /**
     * Set multiple settings at once (e.g. from form). Refreshes cache once.
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $data) {
            $value = $data['value'] ?? null;
            $type = $data['type'] ?? 'string';
            $group = $data['group_name'] ?? 'general';
            $this->set($key, $value, $type, $group);
        }
    }

    /**
     * Clear cache (e.g. after bulk update). Usually called internally by set/setMany.
     */
    public function refreshCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get all settings as array key => ['value' => ..., 'type' => ..., 'group_name' => ...].
     */
    public function getAllCached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return SystemSetting::all()
                ->keyBy('key')
                ->map(fn ($row) => [
                    'value' => $row->value,
                    'type' => $row->type,
                    'group_name' => $row->group_name,
                ])
                ->toArray();
        });
    }

    private function castValue(mixed $value, string $type): mixed
    {
        if ($value === null || $value === '') {
            return $type === 'boolean' ? false : null;
        }
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            default:
                return $value;
        }
    }

    private function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'integer':
                return (string) (int) $value;
            case 'json':
                return is_string($value) ? $value : json_encode($value);
            default:
                return (string) $value;
        }
    }
}

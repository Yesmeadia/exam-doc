<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Runtime static cache to avoid redundant queries within the same request.
     */
    protected static ?array $runtimeCache = null;

    /**
     * Get a setting value by key with optional fallback.
     */
    public static function get(string $key, $default = null)
    {
        $all = self::getAllSettings();

        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }

        return $default;
    }

    /**
     * Get all settings as a plain associative array.
     */
    public static function getAllSettings(): array
    {
        if (static::$runtimeCache !== null) {
            return static::$runtimeCache;
        }

        try {
            $settings = Cache::rememberForever('app_settings_array_v3', function () {
                if (!Schema::hasTable('settings')) {
                    return [];
                }
                return self::query()->pluck('value', 'key')->toArray();
            });

            if (!is_array($settings)) {
                $settings = self::query()->pluck('value', 'key')->toArray();
            }

            static::$runtimeCache = $settings;

            return $settings;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Set a setting value by key and clear cache.
     */
    public static function set(string $key, $value, string $group = 'general'): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        self::clearSettingsCache();

        return $setting;
    }

    /**
     * Clear both runtime and persisted settings cache.
     */
    public static function clearSettingsCache(): void
    {
        static::$runtimeCache = null;
        Cache::forget('app_settings_all');
        Cache::forget('app_settings_array_v2');
        Cache::forget('app_settings_array_v3');
    }

    /**
     * Alias for clearSettingsCache.
     */
    public static function clearCache(): void
    {
        self::clearSettingsCache();
    }
}

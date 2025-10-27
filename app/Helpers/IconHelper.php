<?php

namespace App\Helpers;

class IconHelper
{
    /**
     * Get all icons
     */
    public static function all(): array
    {
        return config('icons');
    }

    /**
     * Get icon by key
     */
    public static function get(string $key): ?array
    {
        return config("icons.{$key}");
    }

    /**
     * Get icon path by key
     */
    public static function path(string $key): string
    {
        $icon = self::get($key);
        return $icon['path'] ?? 'M4 6h16M4 12h16M4 18h16';
    }

    /**
     * Get icon name by key
     */
    public static function name(string $key): string
    {
        $icon = self::get($key);
        return $icon['name'] ?? $key;
    }

    /**
     * Get icons as JSON for JavaScript
     */
    public static function toJson(): string
    {
        return json_encode(self::all());
    }

    /**
     * Check if icon exists
     */
    public static function exists(string $key): bool
    {
        return config("icons.{$key}") !== null;
    }
}

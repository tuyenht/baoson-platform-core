<?php

namespace App\Helpers;

class TranslationHelper
{
    /**
     * Cache for loaded translations
     *
     * @var array<string, array<string, array<string, string>>>
     */
    private static array $cache = [];

    /**
     * Load translations from JSON file (faster than PHP)
     *
     * @param  string  $locale
     * @param  string  $namespace
     * @return array<string, string>
     */
    public static function load(string $locale, string $namespace = 'auth'): array
    {
        $cacheKey = "{$locale}.{$namespace}";

        // Return from cache if available
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $path = resource_path("lang/{$locale}/{$namespace}.json");

        if (! file_exists($path)) {
            self::$cache[$cacheKey] = [];
            return [];
        }

        $content = file_get_contents($path);
        $translations = json_decode($content, true) ?? [];

        // Cache the result
        self::$cache[$cacheKey] = $translations;

        return $translations;
    }

    /**
     * Load all translations for all locales
     *
     * @param  array<int, string>  $locales
     * @param  string  $namespace
     * @return array<string, array<string, string>>
     */
    public static function loadAll(array $locales, string $namespace = 'auth'): array
    {
        $result = [];

        foreach ($locales as $locale) {
            $result[$locale] = [
                $namespace => self::load($locale, $namespace),
            ];
        }

        return $result;
    }

    /**
     * Clear cache (useful for development)
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}


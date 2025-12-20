/**
 * Client-side translation loader - EAGER load, synchronous access
 * All translations in one JSON file, loaded immediately when module loads
 */

import translationsData from '../locales/translations.json';

type SupportedLocale = 'en' | 'vi' | 'ja' | 'zh';

type Translations = {
    auth: Record<string, string>;
};

type AllTranslations = {
    [locale in SupportedLocale]: Record<string, string>;
};

// EAGER load - translations are available immediately (no async needed)
const allTranslationsCache: AllTranslations = translationsData as AllTranslations;

/**
 * Get translations for a locale (synchronous, instant)
 */
export function getTranslations(locale: SupportedLocale): Translations {
    return {
        auth: allTranslationsCache[locale] || {},
    };
}

/**
 * Check if translations are loaded (always true now - eager loaded)
 */
export function isLoaded(): boolean {
    return true;
}

/**
 * Get all translations (for debugging or advanced use)
 */
export function getAllTranslations(): AllTranslations {
    return allTranslationsCache;
}

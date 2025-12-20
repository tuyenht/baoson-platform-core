import { useEffect, useState } from 'react';
import { useLocale } from '@/contexts/LocaleContext';
import { getTranslations } from '@/utils/translations';

type Translations = {
    auth: Record<string, string>;
};

export function useI18n() {
    const { locale: contextLocale } = useLocale();
    
    // Local state to force re-render when locale changes
    const [renderKey, setRenderKey] = useState(0);
    
    // Subscribe to locale changes and force re-render
    useEffect(() => {
        console.log('[useI18n] Locale changed to:', contextLocale);
        setRenderKey(prev => prev + 1);
    }, [contextLocale]);
    
    // Get translations synchronously
    const translations = getTranslations(contextLocale);

    // t function - always get fresh translations
    const t = (key: string): string => {
        // Get fresh translations for current locale
        const currentTranslations = getTranslations(contextLocale);
        
        // Support both "auth.welcome" and "welcome" (defaults to auth namespace)
        const parts = key.includes('.') ? key.split('.') : ['auth', key];
        const [namespace, ...keyParts] = parts;
        const finalKey = keyParts.join('.');

        const ns = currentTranslations?.[namespace as keyof Translations];
        if (!ns) {
            return key;
        }

        const keys = finalKey.split('.');
        let value: unknown = ns;

        for (const k of keys) {
            if (typeof value === 'object' && value !== null && k in value) {
                value = (value as Record<string, unknown>)[k];
            } else {
                return key;
            }
        }

        return typeof value === 'string' ? value : key;
    };

    // Return object - include renderKey to ensure new object when locale changes
    return {
        locale: contextLocale,
        t,
        translations,
        _renderKey: renderKey, // Internal key to force re-render
    };
}

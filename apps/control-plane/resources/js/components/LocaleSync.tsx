import { useEffect, useRef } from 'react';
import { usePage } from '@inertiajs/react';
import { useLocale } from '@/contexts/LocaleContext';
import type { PageProps } from '@/types';

/**
 * Component to sync locale from Inertia page props to LocaleProvider
 * This must be rendered inside Inertia app context (can use usePage)
 * 
 * IMPORTANT: Only sync on initial load, not when user manually changes locale
 */
export default function LocaleSync() {
    const { locale: pageLocale } = usePage<PageProps>().props;
    const { syncLocale, locale: currentLocale } = useLocale();
    const hasSyncedRef = useRef(false);

    // Sync locale from page props ONLY on initial mount (first render)
    // After that, user's manual changes take precedence
    useEffect(() => {
        if (!hasSyncedRef.current && pageLocale && pageLocale !== currentLocale) {
            console.log('[LocaleSync] Initial sync from server locale:', pageLocale);
            syncLocale(pageLocale as 'en' | 'vi' | 'ja' | 'zh');
            hasSyncedRef.current = true;
        }
    }, []); // Empty deps - only run once on mount

    return null; // This component doesn't render anything
}

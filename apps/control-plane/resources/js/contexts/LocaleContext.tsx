import { createContext, ReactNode, useContext, useEffect, useRef, useState } from 'react';
import type { PageProps } from '@/types';

type SupportedLocale = 'en' | 'vi' | 'ja' | 'zh';

interface LocaleContextType {
    locale: SupportedLocale;
    setLocale: (locale: SupportedLocale) => void;
    syncLocale: (locale: SupportedLocale) => void;
}

const LocaleContext = createContext<LocaleContextType | undefined>(undefined);

interface LocaleProviderProps {
    children: ReactNode;
    initialLocale?: string;
    admin?: PageProps['admin'];
}

export function LocaleProvider({ 
    children, 
    initialLocale, 
    admin
}: LocaleProviderProps) {
    const [locale, setLocaleState] = useState<SupportedLocale>(
        (initialLocale as SupportedLocale) || 'en'
    );
    
    // Track if user has manually changed locale (to prevent server sync from overriding)
    const userChangedLocaleRef = useRef(false);
    
    // Debounce timer for saving locale to server
    const saveTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const adminRef = useRef(admin);

    // Update admin ref when it changes
    useEffect(() => {
        adminRef.current = admin;
    }, [admin]);

    // Sync with server locale ONLY on initial load or navigation (not when user manually changes)
    useEffect(() => {
        if (!userChangedLocaleRef.current && initialLocale && initialLocale !== locale) {
            setLocaleState(initialLocale as SupportedLocale);
        }
    }, [initialLocale, locale]);

    // Update document lang attribute with proper format for CSS selectors
    useEffect(() => {
        if (typeof document !== 'undefined') {
            // Map locale codes to proper HTML lang attribute values
            // CSS selectors use: html:lang(zh-CN), html:lang(zh-Hans), html:lang(zh) for Chinese
            // and html:lang(ja) for Japanese
            const langMap: Record<SupportedLocale, string> = {
                'en': 'en',
                'vi': 'vi',
                'ja': 'ja',
                'zh': 'zh-CN', // Use zh-CN for Simplified Chinese (matches CSS selector)
            };
            document.documentElement.lang = langMap[locale] || locale;
        }
    }, [locale]);

    // Cleanup timer on unmount
    useEffect(() => {
        return () => {
            if (saveTimerRef.current) {
                clearTimeout(saveTimerRef.current);
            }
        };
    }, []);

    // setLocale function - instant UI update, background server save
    const setLocale = (newLocale: SupportedLocale) => {
        if (newLocale === locale) {
            return;
        }

        // Mark that user has manually changed locale
        userChangedLocaleRef.current = true;

        // Instant UI update
        setLocaleState(newLocale);

        // Clear previous timer if exists
        if (saveTimerRef.current) {
            clearTimeout(saveTimerRef.current);
        }

        // Save to server in background with debounce (non-blocking, no page reload)
        const localeStoreRoute = adminRef.current?.routes?.localeStore ?? 'locale.store';
        const url = route(localeStoreRoute);
        
        saveTimerRef.current = setTimeout(() => {
            // Use axios directly to avoid Inertia navigation/reload
            window.axios
                .post(url, { locale: newLocale })
                .catch(() => {
                    // Silently fail - user already sees the change, no need to revert
                });
        }, 300);
    };

    // syncLocale function - for server sync only
    const syncLocale = (newLocale: SupportedLocale) => {
        if (newLocale !== locale && !userChangedLocaleRef.current) {
            setLocaleState(newLocale);
        }
    };

    // Context value - create new object each render
    // React will detect when locale changes and re-render consumers
    const contextValue: LocaleContextType = {
        locale,
        setLocale,
        syncLocale,
    };

    return (
        <LocaleContext.Provider value={contextValue}>
            {children}
        </LocaleContext.Provider>
    );
}

export function useLocale() {
    const context = useContext(LocaleContext);
    if (!context) {
        throw new Error('useLocale must be used within LocaleProvider');
    }
    return context;
}

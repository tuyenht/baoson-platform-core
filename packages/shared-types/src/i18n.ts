/**
 * Internationalization (i18n) types
 */

export type Locale = string;

export type TranslationKey = string;

export type TranslationValue = string | Record<string, TranslationValue>;

export type Translations = Record<Locale, Record<TranslationKey, TranslationValue>>;

export type LocaleConfig = {
  code: Locale;
  name: string;
  nativeName: string;
  flag?: string;
  isRTL?: boolean;
  dateFormat: string;
  timeFormat: string;
};


import { useLocale } from '@/contexts/LocaleContext';

type SupportedLocale = 'en' | 'vi' | 'ja' | 'zh';

const locales: SupportedLocale[] = ['en', 'vi', 'ja', 'zh'];

// Language names mapping for tooltip
const languageNames: Record<SupportedLocale, string> = {
    en: 'English',
    vi: 'Tiếng Việt',
    ja: '日本語',
    zh: '中文',
};

export default function LanguageSwitcher() {
    const { locale, setLocale } = useLocale();

    const handleLocaleClick = (e: React.MouseEvent<HTMLButtonElement>, newLocale: SupportedLocale) => {
        e.preventDefault();
        e.stopPropagation();
        setLocale(newLocale);
    };

    return (
        <div className="
            relative w-full flex justify-center mb-4 md:mb-6 z-50
            md:absolute md:top-6 md:right-6 md:w-auto md:block
        ">
            <div className="flex gap-1 bg-slate-900/30 backdrop-blur-xl p-1 md:p-1.5 rounded-full border border-white/10 shadow-2xl">
                {locales.map((l) => (
                    <button
                        key={l}
                        onClick={(e) => handleLocaleClick(e, l)}
                        type="button"
                        className={`
                            relative group
                            w-7 h-7 md:w-9 md:h-9 flex items-center justify-center
                            text-[10px] md:text-xs font-bold rounded-full transition-all duration-300 ease-out cursor-pointer
                            ${locale === l
                                ? 'bg-white text-blue-700 shadow-lg scale-105'
                                : 'text-white/60 hover:text-white hover:bg-white/10'}
                        `}
                    >
                        {l.toUpperCase()}

                        {/* Custom Tooltip - Only show on desktop */}
                        <div className="
                            absolute top-[140%] left-1/2 -translate-x-1/2 px-3.5 py-2
                            bg-white/95 backdrop-blur-xl
                            text-slate-800 text-[11px] font-bold tracking-wide whitespace-nowrap rounded-lg
                            border border-white/40 shadow-[0_8px_30px_rgb(0,0,0,0.12)]
                            opacity-0 invisible group-hover:opacity-100 group-hover:visible
                            transition-all duration-300 transform translate-y-2 group-hover:translate-y-0
                            pointer-events-none hidden md:block
                        ">
                            {/* Tooltip Arrow */}
                            <div className="absolute bottom-full left-1/2 -translate-x-1/2 border-[6px] border-transparent border-b-white/95"></div>
                            {languageNames[l]}
                        </div>
                    </button>
                ))}
            </div>
        </div>
    );
}

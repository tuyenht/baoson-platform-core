import { createLogger, defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

const logger = createLogger();
const FONT_RESOLVE_WARN_RE =
    /\/fonts\/.+\.(woff2|woff|ttf|otf)\s+referenced in\s+.*didn't resolve at build time/i;

function extractWarnText(msg: unknown): string {
    if (typeof msg === 'string') {
        return msg;
    }
    if (msg instanceof Error) {
        return msg.message;
    }
    if (msg && typeof msg === 'object' && 'message' in msg) {
        const m = (msg as { message?: unknown }).message;
        return typeof m === 'string' ? m : String(m ?? '');
    }
    return '';
}

export default defineConfig({
    customLogger: {
        ...logger,
        warn(msg, options) {
            // Suppress expected warnings for public/fonts assets referenced from CSS.
            const text = extractWarnText(msg);
            if (
                text &&
                (FONT_RESOLVE_WARN_RE.test(text) ||
                    (text.includes("/fonts/") && text.includes("didn't resolve at build time")))
            ) {
                return;
            }

            logger.warn(msg, options);
        },
    },
    // Allow Vite to resolve absolute URLs like /fonts/* against public/ at build time.
    // We keep copyPublicDir disabled to avoid copying public/ into public/build.
    publicDir: 'public',
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    /**
     * Windows thường gặp case Vite dev server ghi hot URL dạng IPv6 ([::1]),
     * trong khi server chỉ bind IPv4 (127.0.0.1) => trang trắng vì không load được JS.
     */
    server: {
        host: '127.0.0.1',
        hmr: { host: '127.0.0.1' },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    build: {
        copyPublicDir: false,
        // Suppress warnings about public folder assets (fonts) not resolving at build time
        // These are served directly by Laravel/web server, not processed by Vite
        rollupOptions: {
            onwarn(warning, warn) {
                // Ignore warnings about public folder assets
                if (warning.message.includes("didn't resolve at build time")) {
                    return;
                }
                warn(warning);
            },
        },
    },
});


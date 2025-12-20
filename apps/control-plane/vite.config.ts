import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
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


import '../css/app.css';
import './bootstrap';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { LocaleProvider } from './contexts/LocaleContext';
import LocaleSync from './components/LocaleSync';
import type { PageProps } from './types';
import { ComponentType } from 'react';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Wrapper component to include LocaleSync inside Inertia context
function withLocaleSync<P extends Record<string, unknown>>(Component: ComponentType<P>) {
    return function WrappedComponent(props: P) {
        return (
            <>
                <LocaleSync />
                <Component {...props} />
            </>
        );
    };
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const page = resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx')
        );
        
        // Wrap page component with LocaleSync
        return page.then((module: unknown) => {
            const typedModule = module as { default: ComponentType<PageProps> };
            const Component = typedModule.default;
            return {
                default: withLocaleSync(Component),
            };
        });
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        // Get props from initialPage (available outside Inertia context)
        const pageProps = props.initialPage.props as PageProps;
        
        root.render(
            <LocaleProvider
                initialLocale={pageProps.locale}
                admin={pageProps.admin}
            >
                <App {...props} />
            </LocaleProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

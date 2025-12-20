import { AxiosInstance } from 'axios';
import { route as ziggyRoute } from 'ziggy-js';

declare global {
    interface Window {
        axios: AxiosInstance;
    }

    var route: typeof ziggyRoute;
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>
> = T & {
    appName?: string;
    csrfToken?: string;
    locale?: string;
    admin?: {
        prefix: string;
        routes: {
            dashboard: string;
            login: string;
            loginStore: string;
            logout: string;
            passwordRequest: string;
            passwordEmail: string;
            passwordReset: string;
            passwordUpdate: string;
            oauthRedirect: string;
            localeStore: string;
        };
    };
    auth?: {
        user: User | null;
    };
    flash?: {
        status?: string | null;
        error?: string | null;
    };
    social?: {
        googleEnabled: boolean;
        facebookEnabled: boolean;
        socialiteInstalled: boolean;
    };
};


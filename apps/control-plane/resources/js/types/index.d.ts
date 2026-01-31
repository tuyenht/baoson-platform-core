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
    two_factor_enabled?: boolean;
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
            usersIndex: string;
            auditLogsIndex: string;
            login: string;
            loginStore: string;
            twoFactorChallenge: string;
            twoFactorChallengeStore: string;
            twoFactorEnable: string;
            twoFactorConfirm: string;
            twoFactorDisable: string;
            twoFactorRecovery: string;
            twoFactorSettings: string;
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
        roles: string[];
        permissions: string[];
    };
    flash?: {
        status?: string | null;
        error?: string | null;
        recoveryCodes?: string[] | null;
    };
    /**
     * Inertia validation errors (when server responds with 422 for form submissions).
     */
    errors?: Record<string, string>;
    social?: {
        googleEnabled: boolean;
        facebookEnabled: boolean;
        socialiteInstalled: boolean;
    };
};


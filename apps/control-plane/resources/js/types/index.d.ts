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
    admin?: {
        prefix: string;
        routes: {
            dashboard: string;
        };
    };
    auth?: {
        user: User | null;
    };
};


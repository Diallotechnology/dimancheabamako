import type { AxiosStatic } from 'axios';
import type { SharedProps } from '@/types';

declare global {
    interface Window {
        axios: AxiosStatic;
    }
}

declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: SharedProps;
    }
}

export {};

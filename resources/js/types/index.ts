export * from './models';

/** Lien du paginateur Laravel (`LengthAwarePaginator::toArray()['links']`). Le libellé contient des entités HTML (`&laquo;`). */
export interface PaginationLink {
    url: string | null;
    label: string;
    page: number | null;
    active: boolean;
}

/** Sortie brute de `->paginate()` envoyée telle quelle à Inertia. */
export interface Paginated<T> {
    data: T[];
    current_page: number;
    from: number | null;
    to: number | null;
    last_page: number;
    per_page: number;
    total: number;
    path: string;
    first_page_url: string;
    last_page_url: string;
    next_page_url: string | null;
    prev_page_url: string | null;
    links: PaginationLink[];
}

/** Option de liste produite par `App\Support\Options::from()` : la valeur est toujours une chaîne. */
export interface Option {
    label: string;
    value: string;
}

export type Role = 'Administrateur' | 'Secretaire' | 'Client';

/** `HandleInertiaRequests::share()['auth']['user']`. */
export interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: Role;
}

export interface SharedProps {
    auth: { user: AuthUser | null };
    locale: string | null;
    devise: string | null;
    flash: string;
    /** Boutique en mode maintenance (`php artisan down`). */
    maintenance: boolean;
}

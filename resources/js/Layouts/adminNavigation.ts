import {
    BadgePercent,
    Banknote,
    Globe2,
    Images,
    LayoutDashboard,
    Link2,
    MapPinned,
    Package,
    ReceiptText,
    Shapes,
    Truck,
    UserCog,
    Users,
} from '@lucide/vue';
import type { Component } from 'vue';
import AdminController from '@/actions/App/Http/Controllers/AdminController';
import ProductImportController from '@/actions/App/Http/Controllers/ProductImportController';
import type { Role } from '@/types';
import type { RouteDefinition } from '@/wayfinder';

export interface NavLeaf {
    title: string;
    href: RouteDefinition<'get'>;
}

export interface NavEntry {
    title: string;
    icon: Component;
    href?: RouteDefinition<'get'>;
    children?: NavLeaf[];
}

export interface NavSection {
    title: string;
    /** Rôles autorisés, alignés sur les blocs `role:` de routes/web.php. */
    roles: Role[];
    entries: NavEntry[];
}

/*
 * Le secrétaire accède au bloc « role:Secretaire » (l'admin aussi, cf. RoleMiddleware).
 * Paybylink est rangé côté administration : sa route est réservée à l'admin.
 */
export const adminNavigation: NavSection[] = [
    {
        title: 'Boutique',
        roles: ['Administrateur', 'Secretaire'],
        entries: [
            {
                title: 'Tableau de bord',
                icon: LayoutDashboard,
                href: AdminController.dashboard(),
            },
            {
                title: 'Ventes',
                icon: ReceiptText,
                href: AdminController.order(),
            },
            {
                title: 'Produits',
                icon: Package,
                children: [
                    { title: 'Tous les produits', href: AdminController.product() },
                    {
                        title: 'En stock',
                        href: AdminController.product({ query: { status: 0 } }),
                    },
                    {
                        title: 'Sur commande',
                        href: AdminController.product({ query: { status: 1 } }),
                    },
                    {
                        title: 'Favoris',
                        href: AdminController.product({ query: { favoris: 1 } }),
                    },
                    {
                        title: 'Import par photos',
                        href: ProductImportController.create(),
                    },
                ],
            },
            {
                title: 'Catégories',
                icon: Shapes,
                href: AdminController.category(),
            },
        ],
    },
    {
        title: 'Administration',
        roles: ['Administrateur'],
        entries: [
            {
                title: 'Clients',
                icon: Users,
                href: AdminController.client(),
            },
            {
                title: 'Promotions',
                icon: BadgePercent,
                href: AdminController.promotion(),
            },
            {
                title: 'Paiements par lien',
                icon: Link2,
                href: AdminController.paylink(),
            },
            {
                title: 'Livraison',
                icon: Truck,
                children: [
                    { title: 'Transporteurs', href: AdminController.transport() },
                    { title: 'Tranches de poids', href: AdminController.poids() },
                    { title: 'Tarifs', href: AdminController.shipping() },
                ],
            },
            {
                title: 'Zones',
                icon: MapPinned,
                href: AdminController.zone(),
            },
            {
                title: 'Pays',
                icon: Globe2,
                href: AdminController.country(),
            },
            {
                title: 'Devises',
                icon: Banknote,
                href: AdminController.devise(),
            },
            {
                title: 'Comptes',
                icon: UserCog,
                children: [
                    { title: 'Équipe', href: AdminController.user() },
                    { title: 'Clients inscrits', href: AdminController.customer() },
                ],
            },
            {
                title: "Visuels d'accueil",
                icon: Images,
                href: AdminController.slide(),
            },
        ],
    },
];

const toUrl = (href: string): URL => new URL(href, window.location.origin);

/**
 * Lien actif : même chemin, et chaque paramètre de query du lien présent dans l'URL courante.
 * Un lien sans query (« Tous les produits ») n'est actif que si l'URL courante n'a aucun des
 * filtres portés par ses voisins : sinon deux entrées s'allumeraient en même temps.
 */
export const isNavActive = (
    href: RouteDefinition<'get'>,
    currentUrl: string,
    siblings: NavLeaf[] = [],
): boolean => {
    const target = toUrl(href.url);
    const current = toUrl(currentUrl);

    if (target.pathname !== current.pathname) {
        return false;
    }

    const targetParams = [...target.searchParams.entries()];

    if (targetParams.length > 0) {
        return targetParams.every(([key, value]) => current.searchParams.get(key) === value);
    }

    return !siblings.some((sibling) => {
        const params = [...toUrl(sibling.href.url).searchParams.keys()];

        // useFilter écrit aussi les filtres vides (`status=`) : seuls les filtres renseignés comptent.
        return params.length > 0 && params.every((key) => (current.searchParams.get(key) ?? '') !== '');
    });
};

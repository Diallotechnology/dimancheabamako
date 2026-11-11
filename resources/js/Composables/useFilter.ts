import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { RouteDefinition } from '@/wayfinder';

type FilterValues = Record<string, string>;

/**
 * Cible des rechargements : action Wayfinder sans paramètre (`AdminController.category`)
 * ou, pour les pages pas encore migrées, URL en chaîne. Absente, la page courante est rechargée.
 */
type FilterTarget = string | (() => RouteDefinition<'get'>) | null;

const resolveUrl = (target: FilterTarget): string => {
    if (typeof target === 'function') {
        return target().url;
    }

    return target ?? window.location.pathname;
};

/**
 * Filtres de liste synchronisés avec la query string : initialisés depuis l'URL
 * (rechargement, lien partagé, retour arrière) puis renvoyés au serveur après un délai.
 */
export function useFilter<T extends FilterValues>(
    defaults: T,
    target: FilterTarget = null,
    debounce = 450,
): { filters: Ref<T>; resetFilters: () => void } {
    const initial = { ...defaults };

    if (typeof window !== 'undefined') {
        const query = new URLSearchParams(window.location.search);

        (Object.keys(initial) as (keyof T)[]).forEach((key) => {
            const value = query.get(key as string);

            if (value !== null) {
                initial[key] = value as T[keyof T];
            }
        });
    }

    const filters = ref(initial) as Ref<T>;
    let timeout: ReturnType<typeof setTimeout> | undefined;

    watch(
        filters,
        (values) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                router.get(resolveUrl(target), values, {
                    preserveState: true,
                    replace: true,
                });
            }, debounce);
        },
        { deep: true },
    );

    const resetFilters = (): void => {
        (Object.keys(filters.value) as (keyof T)[]).forEach((key) => {
            filters.value[key] = '' as T[keyof T];
        });
    };

    return { filters, resetFilters };
}

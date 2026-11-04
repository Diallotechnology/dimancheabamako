import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

export function useFilter(defaults = {}, url = null, debounce = 450) {
    // Initialise les filtres depuis la query string (rechargement, lien partagé, retour arrière).
    const initial = { ...defaults };
    if (typeof window !== "undefined") {
        const query = new URLSearchParams(window.location.search);
        Object.keys(initial).forEach((key) => {
            if (query.has(key)) {
                initial[key] = query.get(key);
            }
        });
    }
    const filters = ref(initial);

    let timeout = null;
    watch(
        filters,
        (val) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                router.get(url ?? route().current(), val, {
                    preserveState: true,
                    replace: true,
                });
            }, debounce);
        },
        { deep: true }
    );

    const resetFilters = () => {
        Object.keys(filters.value).forEach((key) => (filters.value[key] = ""));
    };

    return { filters, resetFilters };
}

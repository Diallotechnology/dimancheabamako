import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

export function useFilter(defaults = {}, url = null, debounce = 450) {
    const filters = ref({ ...defaults });

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

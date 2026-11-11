import { onBeforeUnmount, ref } from 'vue';
import type { Ref } from 'vue';
import { get_trans_zone } from '@/actions/App/Http/Controllers/TransportController';
import type { Option } from '@/types';

/**
 * Zones desservies par un transporteur, chargées à la demande (liste dépendante : transporteur → zone).
 * Un chargement plus récent annule le précédent, pour qu'une réponse tardive d'un ancien transporteur
 * n'écrase jamais la liste du transporteur courant.
 */
export function useTransportZones(): {
    zones: Ref<Option[]>;
    loading: Ref<boolean>;
    failed: Ref<boolean>;
    load: (transportId: string) => Promise<void>;
    clear: () => void;
} {
    const zones = ref<Option[]>([]);
    const loading = ref(false);
    const failed = ref(false);
    let controller: AbortController | null = null;

    const clear = (): void => {
        controller?.abort();
        controller = null;
        zones.value = [];
        loading.value = false;
        failed.value = false;
    };

    const load = async (transportId: string): Promise<void> => {
        clear();

        if (transportId === '') {
            return;
        }

        const current = new AbortController();
        controller = current;
        loading.value = true;

        try {
            const response = await fetch(get_trans_zone.url(Number(transportId)), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: current.signal,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            zones.value = (await response.json()) as Option[];
        } catch (error) {
            if (current.signal.aborted) {
                return;
            }

            failed.value = true;
        } finally {
            if (controller === current) {
                loading.value = false;
            }
        }
    };

    onBeforeUnmount(clear);

    return { zones, loading, failed, load, clear };
}

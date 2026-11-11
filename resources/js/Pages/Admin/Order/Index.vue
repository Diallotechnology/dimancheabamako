<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Eye, Search, X } from '@lucide/vue';
import { order as orderIndex } from '@/actions/App/Http/Controllers/AdminController';
import { destroy, show } from '@/actions/App/Http/Controllers/OrderController';
import ConfirmDelete from '@/Components/Admin/ConfirmDelete.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { NativeSelect } from '@/Components/ui/native-select';
import { TableCell, TableHead } from '@/Components/ui/table';
import { useFilter } from '@/Composables/useFilter';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import { formatXof } from '@/lib/format';
import type { Client, Option, Order, Paginated } from '@/types';

const props = defineProps<{
    rows: Paginated<Order>;
    client: Pick<Client, 'id' | 'nom' | 'prenom'>[];
    /** États possibles : `id` et `name` portent le même libellé (`OrderEnum::all()`). */
    order_status: { id: string; name: string }[];
}>();

const { filters, resetFilters } = useFilter(
    {
        search: '',
        client: '',
        status: '',
        date: '',
    },
    orderIndex,
);

const hasFilter = (): boolean => Object.values(filters.value).some((value) => value !== '');

const clientOptions: Option[] = props.client.map((item) => ({
    label: `${item.prenom} ${item.nom}`,
    value: String(item.id),
}));

// Supprimer la dernière ligne d'une page > 1 ramène à la page précédente.
const afterDelete = (): void => {
    if (props.rows.data.length === 1 && props.rows.current_page > 1) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', String(props.rows.current_page - 1));
        router.get(url.pathname + url.search, {}, { preserveState: true, replace: true });
    }
};
</script>

<template>
    <AdminLayoutV2 title="Ventes" description="Les commandes de la boutique : paiement, livraison et état.">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-xs">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Rechercher une vente…"
                    aria-label="Rechercher une vente"
                    class="pl-9"
                />
            </div>
            <NativeSelect v-model="filters.client" aria-label="Filtrer par client">
                <option value="">Tous les clients</option>
                <option v-for="item in clientOptions" :key="item.value" :value="item.value">{{ item.label }}</option>
            </NativeSelect>
            <NativeSelect v-model="filters.status" aria-label="Filtrer par état">
                <option value="">Tous les états</option>
                <option v-for="item in order_status" :key="item.id" :value="item.id">{{ item.name }}</option>
            </NativeSelect>
            <Input v-model="filters.date" type="date" aria-label="Filtrer par date" class="w-auto" />
            <Button v-if="hasFilter()" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <DataTable :rows="rows" :columns="9" item-label="ventes">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Facture</TableHead>
                <TableHead>Paiement</TableHead>
                <TableHead>Client</TableHead>
                <TableHead>Livraison</TableHead>
                <TableHead class="text-right">Total</TableHead>
                <TableHead>État</TableHead>
                <TableHead>Date</TableHead>
                <TableHead class="w-24 text-right">Actions</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium whitespace-nowrap">{{ row.reference }}</TableCell>
                <TableCell>
                    <p class="whitespace-nowrap">{{ row.trans_ref }}</p>
                    <Badge v-if="row.trans_state" variant="outline" size="sm">{{ row.trans_state }}</Badge>
                </TableCell>
                <TableCell>
                    <p class="font-medium whitespace-nowrap">{{ row.client?.prenom }} {{ row.client?.nom }}</p>
                    <p class="text-xs text-muted-foreground">{{ row.client?.email }}</p>
                    <p class="text-xs text-muted-foreground">{{ row.client?.contact }}</p>
                </TableCell>
                <TableCell>
                    <p>{{ row.adresse }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ row.postal }} {{ row.ville }}, {{ row.country?.nom }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ row.transport?.nom }} · {{ row.poids }} · {{ formatXof(Number(row.shipping)) }}
                    </p>
                </TableCell>
                <TableCell class="text-right whitespace-nowrap tabular-nums">{{ formatXof(Number(row.totaux ?? 0)) }}</TableCell>
                <TableCell><Badge variant="secondary" size="sm">{{ row.etat }}</Badge></TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
                <TableCell>
                    <div class="flex justify-end gap-1">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Voir la vente ${row.reference}`"
                        >
                            <Link :href="show(row.id)">
                                <Eye />
                            </Link>
                        </Button>
                        <ConfirmDelete
                            :action="destroy(row.id)"
                            :what="`la vente ${row.reference}`"
                            success-message="Vente supprimée."
                            @deleted="afterDelete"
                        />
                    </div>
                </TableCell>
            </template>

            <template #empty>
                <template v-if="hasFilter()">Aucune vente ne correspond à ces filtres.</template>
                <template v-else>Aucune vente pour le moment.</template>
            </template>
        </DataTable>
    </AdminLayoutV2>
</template>

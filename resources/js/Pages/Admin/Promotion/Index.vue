<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Search, X } from '@lucide/vue';
import { promotion as promotionIndex } from '@/actions/App/Http/Controllers/AdminController';
import { create, destroy, edit, show } from '@/actions/App/Http/Controllers/PromotionController';
import ConfirmDelete from '@/Components/Admin/ConfirmDelete.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { TableCell, TableHead } from '@/Components/ui/table';
import { useFilter } from '@/Composables/useFilter';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Paginated, Promotion } from '@/types';

const props = defineProps<{
    /** `debut` et `fin` arrivent déjà formatés (d/m/Y H:i) par AdminController::promotion. */
    rows: Paginated<Promotion>;
}>();

const { filters, resetFilters } = useFilter({ search: '' }, promotionIndex);

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
    <AdminLayoutV2 title="Promotions" description="Les réductions appliquées à une sélection de produits, sur une période.">
        <template #actions>
            <Button as-child>
                <Link :href="create()">
                    <Plus />
                    Nouvelle promotion
                </Link>
            </Button>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Rechercher une promotion…"
                    aria-label="Rechercher une promotion"
                    class="pl-9"
                />
            </div>
            <Button v-if="filters.search" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <DataTable :rows="rows" :columns="7" item-label="promotions">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Nom</TableHead>
                <TableHead>Réduction</TableHead>
                <TableHead>Début</TableHead>
                <TableHead>Fin</TableHead>
                <TableHead>Créée le</TableHead>
                <TableHead class="w-32 text-right">Actions</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium">{{ row.nom }}</TableCell>
                <TableCell>{{ row.reduction }} %</TableCell>
                <TableCell class="whitespace-nowrap">{{ row.debut }}</TableCell>
                <TableCell class="whitespace-nowrap">{{ row.fin }}</TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
                <TableCell>
                    <div class="flex justify-end gap-1">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Voir la promotion ${row.nom}`"
                        >
                            <a :href="show(row.id).url" @click.prevent="router.visit(show(row.id))">
                                <Eye />
                            </a>
                        </Button>
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Modifier la promotion ${row.nom}`"
                        >
                            <a :href="edit(row.id).url" @click.prevent="router.visit(edit(row.id))">
                                <Pencil />
                            </a>
                        </Button>
                        <ConfirmDelete
                            :action="destroy(row.id)"
                            :what="`la promotion ${row.nom}`"
                            success-message="Promotion supprimée."
                            @deleted="afterDelete"
                        />
                    </div>
                </TableCell>
            </template>

            <template #empty>
                <template v-if="filters.search">Aucune promotion ne correspond à « {{ filters.search }} ».</template>
                <div v-else class="flex flex-col items-center gap-3">
                    Aucune promotion pour le moment.
                    <Button as-child size="sm">
                        <Link :href="create()">
                            <Plus />
                            Créer une promotion
                        </Link>
                    </Button>
                </div>
            </template>
        </DataTable>
    </AdminLayoutV2>
</template>

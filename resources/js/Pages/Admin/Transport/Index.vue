<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Search, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { transport as transportIndex } from '@/actions/App/Http/Controllers/AdminController';
import { destroy, edit, store } from '@/actions/App/Http/Controllers/TransportController';
import Combobox from '@/Components/Admin/Combobox.vue';
import ConfirmDelete from '@/Components/Admin/ConfirmDelete.vue';
import CrudDialog from '@/Components/Admin/CrudDialog.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { TableCell, TableHead } from '@/Components/ui/table';
import { useFilter } from '@/Composables/useFilter';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Option, Paginated, Transport } from '@/types';

const props = defineProps<{
    rows: Paginated<Transport>;
    /** Zones de livraison : valeur = id (chaîne). */
    zone: Option[];
}>();

const { filters, resetFilters } = useFilter({ search: '' }, transportIndex);

const creating = ref(false);

const form = useForm<{ nom: string; zone_id: string[] }>({
    nom: '',
    zone_id: [],
});

watch(creating, (open) => {
    if (!open) {
        form.reset();
        form.clearErrors();
    }
});

const submit = (): void => {
    form.submit(store(), {
        onSuccess: () => {
            creating.value = false;
            toast.success('Transporteur ajouté.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};

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
    <AdminLayoutV2 title="Transporteurs" description="Les transporteurs et les zones qu'ils desservent.">
        <template #actions>
            <Button @click="creating = true">
                <Plus />
                Nouveau transporteur
            </Button>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Rechercher un transporteur…"
                    aria-label="Rechercher un transporteur"
                    class="pl-9"
                />
            </div>
            <Button v-if="filters.search" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <DataTable :rows="rows" :columns="5" item-label="transporteurs">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Nom</TableHead>
                <TableHead>Zones</TableHead>
                <TableHead>Créé le</TableHead>
                <TableHead class="w-24 text-right">Actions</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium">{{ row.nom }}</TableCell>
                <TableCell>
                    <div class="flex flex-wrap gap-1">
                        <Badge v-for="item in row.zones" :key="item.id" variant="secondary">
                            {{ item.nom }}
                        </Badge>
                    </div>
                </TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
                <TableCell>
                    <div class="flex justify-end gap-1">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Modifier le transporteur ${row.nom}`"
                        >
                            <a :href="edit(row.id).url" @click.prevent="router.visit(edit(row.id))">
                                <Pencil />
                            </a>
                        </Button>
                        <ConfirmDelete
                            :action="destroy(row.id)"
                            :what="`le transporteur ${row.nom}`"
                            success-message="Transporteur supprimé."
                            @deleted="afterDelete"
                        />
                    </div>
                </TableCell>
            </template>

            <template #empty>
                <template v-if="filters.search">Aucun transporteur ne correspond à « {{ filters.search }} ».</template>
                <div v-else class="flex flex-col items-center gap-3">
                    Aucun transporteur pour le moment.
                    <Button size="sm" @click="creating = true">
                        <Plus />
                        Ajouter un transporteur
                    </Button>
                </div>
            </template>
        </DataTable>

        <CrudDialog
            v-model:open="creating"
            title="Nouveau transporteur"
            submit-label="Ajouter le transporteur"
            :processing="form.processing"
            @submit="submit"
        >
            <FormField label="Nom" :error="form.errors.nom" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.nom"
                    placeholder="Nom du transporteur"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Zones de livraison" :error="form.errors.zone_id" required v-slot="field">
                <Combobox
                    :id="field.id"
                    v-model="form.zone_id"
                    :options="zone"
                    multiple
                    placeholder="Choisir des zones"
                    search-placeholder="Rechercher une zone…"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
        </CrudDialog>
    </AdminLayoutV2>
</template>

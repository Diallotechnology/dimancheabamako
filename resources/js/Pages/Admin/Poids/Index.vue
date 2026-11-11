<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Search, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { poids as poidsIndex } from '@/actions/App/Http/Controllers/AdminController';
import { destroy, edit, store } from '@/actions/App/Http/Controllers/PoidsController';
import ConfirmDelete from '@/Components/Admin/ConfirmDelete.vue';
import CrudDialog from '@/Components/Admin/CrudDialog.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { TableCell, TableHead } from '@/Components/ui/table';
import { useFilter } from '@/Composables/useFilter';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Paginated, Poids } from '@/types';

const props = defineProps<{
    rows: Paginated<Poids>;
}>();

const { filters, resetFilters } = useFilter({ search: '' }, poidsIndex);

const creating = ref(false);

// Champs en texte : la règle serveur `decimal:2` exige la chaîne « 5.00 », qu'un
// <input type="number"> convertirait en nombre (5) avant l'envoi.
const form = useForm({
    min: '',
    max: '',
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
            toast.success('Tranche de poids créée.');
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
    <AdminLayoutV2 title="Poids" description="Les tranches de poids utilisées pour calculer les frais de livraison.">
        <template #actions>
            <Button @click="creating = true">
                <Plus />
                Nouvelle tranche
            </Button>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Rechercher un poids…"
                    aria-label="Rechercher un poids"
                    class="pl-9"
                />
            </div>
            <Button v-if="filters.search" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <DataTable :rows="rows" :columns="5" item-label="tranches">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Minimum</TableHead>
                <TableHead>Maximum</TableHead>
                <TableHead>Créée le</TableHead>
                <TableHead class="w-24 text-right">Actions</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium">{{ row.min }} kg</TableCell>
                <TableCell class="font-medium">{{ row.max }} kg</TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
                <TableCell>
                    <div class="flex justify-end gap-1">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Modifier la tranche de ${row.min} à ${row.max} kg`"
                        >
                            <a :href="edit(row.id).url" @click.prevent="router.visit(edit(row.id))">
                                <Pencil />
                            </a>
                        </Button>
                        <ConfirmDelete
                            :action="destroy(row.id)"
                            :what="`la tranche de ${row.min} à ${row.max} kg`"
                            success-message="Tranche de poids supprimée."
                            @deleted="afterDelete"
                        />
                    </div>
                </TableCell>
            </template>

            <template #empty>
                <template v-if="filters.search">Aucune tranche ne correspond à « {{ filters.search }} ».</template>
                <div v-else class="flex flex-col items-center gap-3">
                    Aucune tranche de poids pour le moment.
                    <Button size="sm" @click="creating = true">
                        <Plus />
                        Créer une tranche
                    </Button>
                </div>
            </template>
        </DataTable>

        <CrudDialog
            v-model:open="creating"
            title="Nouvelle tranche de poids"
            submit-label="Créer la tranche"
            :processing="form.processing"
            @submit="submit"
        >
            <FormField label="Minimum (kg)" hint="Avec deux décimales, par exemple 0.50." :error="form.errors.min" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.min"
                    inputmode="decimal"
                    placeholder="0.00"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Maximum (kg)" hint="Avec deux décimales, par exemple 5.00." :error="form.errors.max" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.max"
                    inputmode="decimal"
                    placeholder="0.00"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
        </CrudDialog>
    </AdminLayoutV2>
</template>

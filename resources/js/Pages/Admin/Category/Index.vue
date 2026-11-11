<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Search, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { category as categoryIndex } from '@/actions/App/Http/Controllers/AdminController';
import { destroy, edit, store } from '@/actions/App/Http/Controllers/CategoryController';
import ConfirmDelete from '@/Components/Admin/ConfirmDelete.vue';
import CrudDialog from '@/Components/Admin/CrudDialog.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { TableCell, TableHead } from '@/Components/ui/table';
import { Textarea } from '@/Components/ui/textarea';
import { useFilter } from '@/Composables/useFilter';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Category, Paginated } from '@/types';

const props = defineProps<{
    rows: Paginated<Category>;
}>();

const { filters, resetFilters } = useFilter({ search: '' }, categoryIndex);

const creating = ref(false);

const form = useForm({
    nom: '',
    description: '',
});

// Fermer la modale (bouton, Échap, clic extérieur) repart d'un formulaire propre.
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
            toast.success('Catégorie créée.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};

// Supprimer la dernière ligne d'une page > 1 ramène sur la page précédente
// au lieu de laisser une page vide.
const afterDelete = (): void => {
    if (props.rows.data.length === 1 && props.rows.current_page > 1) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', String(props.rows.current_page - 1));
        router.get(url.pathname + url.search, {}, { preserveState: true, replace: true });
    }
};
</script>

<template>
    <AdminLayoutV2 title="Catégories" description="Les catégories qui regroupent les produits de la boutique.">
        <template #actions>
            <Button @click="creating = true">
                <Plus />
                Nouvelle catégorie
            </Button>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Rechercher une catégorie…"
                    aria-label="Rechercher une catégorie"
                    class="pl-9"
                />
            </div>
            <Button v-if="filters.search" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <DataTable :rows="rows" :columns="5" item-label="catégories">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Nom</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Créée le</TableHead>
                <TableHead class="w-24 text-right">Actions</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium">{{ row.nom }}</TableCell>
                <TableCell class="max-w-md truncate text-muted-foreground">{{ row.description }}</TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
                <TableCell>
                    <div class="flex justify-end gap-1">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Modifier la catégorie ${row.nom}`"
                        >
                            <a :href="edit(row.id).url" @click.prevent="router.visit(edit(row.id))">
                                <Pencil />
                            </a>
                        </Button>
                        <ConfirmDelete
                            :action="destroy(row.id)"
                            :what="`la catégorie ${row.nom}`"
                            success-message="Catégorie supprimée."
                            @deleted="afterDelete"
                        />
                    </div>
                </TableCell>
            </template>

            <template #empty>
                <template v-if="filters.search">Aucune catégorie ne correspond à « {{ filters.search }} ».</template>
                <div v-else class="flex flex-col items-center gap-3">
                    Aucune catégorie pour le moment.
                    <Button size="sm" @click="creating = true">
                        <Plus />
                        Créer une catégorie
                    </Button>
                </div>
            </template>
        </DataTable>

        <CrudDialog
            v-model:open="creating"
            title="Nouvelle catégorie"
            description="Elle sera proposée lors de la création d'un produit."
            submit-label="Créer la catégorie"
            :processing="form.processing"
            @submit="submit"
        >
            <FormField label="Nom" :error="form.errors.nom" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.nom"
                    placeholder="Le nom de la catégorie"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Description" :error="form.errors.description" required v-slot="field">
                <Textarea
                    :id="field.id"
                    v-model="form.description"
                    rows="3"
                    placeholder="La description courte de la catégorie"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
        </CrudDialog>
    </AdminLayoutV2>
</template>

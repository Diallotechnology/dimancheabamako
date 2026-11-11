<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Search, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { client as clientIndex } from '@/actions/App/Http/Controllers/AdminController';
import { destroy, edit, show, store } from '@/actions/App/Http/Controllers/ClientController';
import Combobox from '@/Components/Admin/Combobox.vue';
import ConfirmDelete from '@/Components/Admin/ConfirmDelete.vue';
import CrudDialog from '@/Components/Admin/CrudDialog.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { TableCell, TableHead } from '@/Components/ui/table';
import { useFilter } from '@/Composables/useFilter';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Client, Option, Paginated } from '@/types';

const props = defineProps<{
    rows: Paginated<Client>;
    country: Option[];
}>();

const { filters, resetFilters } = useFilter({ search: '' }, clientIndex);

const creating = ref(false);

const form = useForm<{ prenom: string; nom: string; email: string; contact: string; pays: string | null }>({
    prenom: '',
    nom: '',
    email: '',
    contact: '',
    pays: null,
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
            toast.success('Client créé.');
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
    <AdminLayoutV2 title="Clients" description="Les clients qui ont passé commande ou ont été créés depuis le back-office.">
        <template #actions>
            <Button @click="creating = true">
                <Plus />
                Nouveau client
            </Button>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Nom, e-mail ou contact…"
                    aria-label="Rechercher un client"
                    class="pl-9"
                />
            </div>
            <Button v-if="filters.search" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <DataTable :rows="rows" :columns="8" item-label="clients">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Prénom</TableHead>
                <TableHead>Nom</TableHead>
                <TableHead>E-mail</TableHead>
                <TableHead>Contact</TableHead>
                <TableHead>Pays</TableHead>
                <TableHead>Créé le</TableHead>
                <TableHead class="w-32 text-right">Actions</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium">{{ row.prenom }}</TableCell>
                <TableCell class="font-medium">{{ row.nom }}</TableCell>
                <TableCell class="text-muted-foreground">{{ row.email }}</TableCell>
                <TableCell class="whitespace-nowrap">{{ row.contact }}</TableCell>
                <TableCell>{{ row.pays }}</TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
                <TableCell>
                    <div class="flex justify-end gap-1">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Voir le client ${row.prenom} ${row.nom}`"
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
                            :aria-label="`Modifier le client ${row.prenom} ${row.nom}`"
                        >
                            <a :href="edit(row.id).url" @click.prevent="router.visit(edit(row.id))">
                                <Pencil />
                            </a>
                        </Button>
                        <ConfirmDelete
                            :action="destroy(row.id)"
                            :what="`le client ${row.prenom} ${row.nom}`"
                            success-message="Client supprimé."
                            @deleted="afterDelete"
                        />
                    </div>
                </TableCell>
            </template>

            <template #empty>
                <template v-if="filters.search">Aucun client ne correspond à « {{ filters.search }} ».</template>
                <div v-else class="flex flex-col items-center gap-3">
                    Aucun client pour le moment.
                    <Button size="sm" @click="creating = true">
                        <Plus />
                        Créer un client
                    </Button>
                </div>
            </template>
        </DataTable>

        <CrudDialog
            v-model:open="creating"
            title="Nouveau client"
            submit-label="Créer le client"
            size="lg"
            :processing="form.processing"
            @submit="submit"
        >
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Prénom" :error="form.errors.prenom" required v-slot="field">
                    <Input
                        :id="field.id"
                        v-model="form.prenom"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedBy"
                    />
                </FormField>
                <FormField label="Nom" :error="form.errors.nom" required v-slot="field">
                    <Input
                        :id="field.id"
                        v-model="form.nom"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedBy"
                    />
                </FormField>
                <FormField label="E-mail" :error="form.errors.email" required v-slot="field">
                    <Input
                        :id="field.id"
                        v-model="form.email"
                        type="email"
                        placeholder="adresse@exemple.com"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedBy"
                    />
                </FormField>
                <FormField label="Contact" :error="form.errors.contact" required v-slot="field">
                    <Input
                        :id="field.id"
                        v-model="form.contact"
                        placeholder="Téléphone"
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedBy"
                    />
                </FormField>
            </div>
            <FormField label="Pays" :error="form.errors.pays" required v-slot="field">
                <Combobox
                    :id="field.id"
                    v-model="form.pays"
                    :options="country"
                    placeholder="Choisir un pays"
                    search-placeholder="Rechercher un pays…"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
        </CrudDialog>
    </AdminLayoutV2>
</template>

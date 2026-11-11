<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Search, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { user as userIndex } from '@/actions/App/Http/Controllers/AdminController';
import { destroy, edit, store } from '@/actions/App/Http/Controllers/UserController';
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
import type { Option, Paginated, User } from '@/types';

const props = defineProps<{
    rows: Paginated<User>;
    roles: Option[];
}>();

const { filters, resetFilters } = useFilter({ search: '' }, userIndex);

const creating = ref(false);

const form = useForm<{ name: string; email: string; role: string | null }>({
    name: '',
    email: '',
    role: null,
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
            toast.success('Utilisateur créé.');
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
    <AdminLayoutV2 title="Utilisateurs" description="Les comptes qui accèdent au back-office : administrateurs et secrétaires.">
        <template #actions>
            <Button @click="creating = true">
                <Plus />
                Nouvel utilisateur
            </Button>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Rechercher par nom ou e-mail…"
                    aria-label="Rechercher un utilisateur"
                    class="pl-9"
                />
            </div>
            <Button v-if="filters.search" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <DataTable :rows="rows" :columns="7" item-label="utilisateurs">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Nom</TableHead>
                <TableHead>E-mail</TableHead>
                <TableHead>Rôle</TableHead>
                <TableHead>État</TableHead>
                <TableHead>Créé le</TableHead>
                <TableHead class="w-24 text-right">Actions</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium">{{ row.name }}</TableCell>
                <TableCell class="text-muted-foreground">{{ row.email }}</TableCell>
                <TableCell>{{ row.role }}</TableCell>
                <TableCell>
                    <Badge v-if="row.etat" variant="success" size="sm">En ligne</Badge>
                    <Badge v-else variant="outline" size="sm">Hors ligne</Badge>
                </TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
                <TableCell>
                    <div class="flex justify-end gap-1">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Modifier l'utilisateur ${row.name}`"
                        >
                            <a :href="edit(row.id).url" @click.prevent="router.visit(edit(row.id))">
                                <Pencil />
                            </a>
                        </Button>
                        <ConfirmDelete
                            :action="destroy(row.id)"
                            :what="`l'utilisateur ${row.name}`"
                            consequence="Son accès au back-office sera définitivement supprimé."
                            success-message="Utilisateur supprimé."
                            @deleted="afterDelete"
                        />
                    </div>
                </TableCell>
            </template>

            <template #empty>
                <template v-if="filters.search">Aucun utilisateur ne correspond à « {{ filters.search }} ».</template>
                <div v-else class="flex flex-col items-center gap-3">
                    Aucun utilisateur pour le moment.
                    <Button size="sm" @click="creating = true">
                        <Plus />
                        Créer un utilisateur
                    </Button>
                </div>
            </template>
        </DataTable>

        <CrudDialog
            v-model:open="creating"
            title="Nouvel utilisateur"
            description="Il pourra se connecter au back-office avec le rôle choisi."
            submit-label="Créer l'utilisateur"
            :processing="form.processing"
            @submit="submit"
        >
            <FormField label="Nom d'utilisateur" :error="form.errors.name" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.name"
                    placeholder="Le nom de l'utilisateur"
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
            <FormField label="Rôle (privilège)" :error="form.errors.role" required v-slot="field">
                <Combobox
                    :id="field.id"
                    v-model="form.role"
                    :options="roles"
                    placeholder="Choisir un rôle"
                    search-placeholder="Rechercher un rôle…"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
        </CrudDialog>
    </AdminLayoutV2>
</template>

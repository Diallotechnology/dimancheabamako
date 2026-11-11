<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Copy, Pencil, Plus, RefreshCw, Search, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { paylink as paylinkIndex } from '@/actions/App/Http/Controllers/AdminController';
import { destroy, edit, regenerate, store } from '@/actions/App/Http/Controllers/PayLinkController';
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
import type { Paginated, PayLink } from '@/types';

const props = defineProps<{
    rows: Paginated<PayLink>;
}>();

const { filters, resetFilters } = useFilter({ search: '' }, paylinkIndex);

const creating = ref(false);
const regeneratingId = ref<number | null>(null);

const form = useForm({
    name: '',
    contact: '',
    montant: '',
});

watch(creating, (open) => {
    if (!open) {
        form.reset();
        form.clearErrors();
    }
});

// `store` et `regenerate` répondent par un simple `back()` même quand N-Genius refuse
// (l'erreur n'est que loguée côté serveur, cf. bug.md) : le succès se lit donc dans les
// données rechargées, pas dans le callback `onSuccess`.
const submit = (): void => {
    const totalBefore = props.rows.total;

    form.submit(store(), {
        onSuccess: (page) => {
            creating.value = false;

            if ((page.props.rows as Paginated<PayLink>).total > totalBefore) {
                toast.success('Lien de paiement créé.');
            } else {
                toast.error('Le lien de paiement n’a pas pu être créé. Réessayez.');
            }
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};

const regenerateLink = (row: PayLink): void => {
    regeneratingId.value = row.id;

    router.post(regenerate(row.id), {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            const updated = (page.props.rows as Paginated<PayLink>).data.find((item) => item.id === row.id);

            if (updated !== undefined && updated.lien !== row.lien) {
                toast.success('Lien de paiement régénéré.');
            } else {
                toast.error('Le lien n’a pas pu être régénéré. Réessayez.');
            }
        },
        onError: () => {
            toast.error('La régénération a échoué.');
        },
        onFinish: () => {
            regeneratingId.value = null;
        },
    });
};

const copyLink = async (link: string): Promise<void> => {
    try {
        await navigator.clipboard.writeText(link);
        toast.success('Lien copié dans le presse-papier.');
    } catch {
        toast.error('Copie impossible : sélectionnez le lien et copiez-le à la main.');

        return;
    }

    // Sur mobile, propose aussi le partage natif ; l'annuler n'est pas une erreur.
    if (typeof navigator.share === 'function') {
        try {
            await navigator.share({ title: 'Lien de paiement', text: 'Expire dans 5 minutes', url: link });
        } catch {
            // partage annulé
        }
    }
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
    <AdminLayoutV2 title="Liens de paiement" description="Générez un lien N-Genius à envoyer à un client ; il expire au bout de 5 minutes.">
        <template #actions>
            <Button @click="creating = true">
                <Plus />
                Nouveau lien
            </Button>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Nom, contact ou montant…"
                    aria-label="Rechercher un lien de paiement"
                    class="pl-9"
                />
            </div>
            <Button v-if="filters.search" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <DataTable :rows="rows" :columns="8" item-label="liens">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Transaction</TableHead>
                <TableHead>Client</TableHead>
                <TableHead class="text-right">Montant (CFA)</TableHead>
                <TableHead>Lien</TableHead>
                <TableHead>État</TableHead>
                <TableHead>Créé le</TableHead>
                <TableHead class="w-36 text-right">Actions</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="text-xs">
                    <div class="font-mono">{{ row.trans_ref }}</div>
                    <div class="text-muted-foreground">{{ row.trans_state }}</div>
                </TableCell>
                <TableCell>
                    <div class="font-medium">{{ row.name }}</div>
                    <div class="text-xs text-muted-foreground">{{ row.contact }}</div>
                </TableCell>
                <TableCell class="text-right tabular-nums">{{ row.montant }}</TableCell>
                <TableCell>
                    <Button
                        variant="outline"
                        size="sm"
                        :aria-label="`Copier le lien de ${row.name}`"
                        @click="copyLink(row.lien)"
                    >
                        <Copy />
                        Copier
                    </Button>
                </TableCell>
                <TableCell><Badge variant="outline" size="sm">{{ row.etat }}</Badge></TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
                <TableCell>
                    <div class="flex justify-end gap-1">
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :disabled="regeneratingId === row.id"
                            :aria-label="`Régénérer le lien de ${row.name}`"
                            @click="regenerateLink(row)"
                        >
                            <RefreshCw :class="{ 'animate-spin': regeneratingId === row.id }" />
                        </Button>
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Modifier le lien de ${row.name}`"
                        >
                            <a :href="edit(row.id).url" @click.prevent="router.visit(edit(row.id))">
                                <Pencil />
                            </a>
                        </Button>
                        <ConfirmDelete
                            :action="destroy(row.id)"
                            :what="`le lien de paiement de ${row.name}`"
                            success-message="Lien de paiement supprimé."
                            @deleted="afterDelete"
                        />
                    </div>
                </TableCell>
            </template>

            <template #empty>
                <template v-if="filters.search">Aucun lien ne correspond à « {{ filters.search }} ».</template>
                <div v-else class="flex flex-col items-center gap-3">
                    Aucun lien de paiement pour le moment.
                    <Button size="sm" @click="creating = true">
                        <Plus />
                        Créer un lien
                    </Button>
                </div>
            </template>
        </DataTable>

        <CrudDialog
            v-model:open="creating"
            title="Nouveau lien de paiement"
            description="Le lien est généré auprès de N-Genius à l’enregistrement."
            submit-label="Générer le lien"
            :processing="form.processing"
            @submit="submit"
        >
            <FormField label="Nom du client" :error="form.errors.name" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.name"
                    placeholder="Le nom du client"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Contact" :error="form.errors.contact" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.contact"
                    placeholder="Téléphone ou e-mail du client"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Montant (CFA)" :error="form.errors.montant" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.montant"
                    type="number"
                    min="1"
                    step="1"
                    placeholder="10000"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
        </CrudDialog>
    </AdminLayoutV2>
</template>

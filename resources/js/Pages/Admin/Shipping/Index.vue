<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Search, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { shipping as shippingIndex } from '@/actions/App/Http/Controllers/AdminController';
import { destroy, edit, store } from '@/actions/App/Http/Controllers/ShippingController';
import Combobox from '@/Components/Admin/Combobox.vue';
import ConfirmDelete from '@/Components/Admin/ConfirmDelete.vue';
import CrudDialog from '@/Components/Admin/CrudDialog.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { useFilter } from '@/Composables/useFilter';
import { useTransportZones } from '@/Composables/useTransportZones';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import { formatXof } from '@/lib/format';
import type { Option, Shipping, Transport } from '@/types';

const props = defineProps<{
    /** Tarifs regroupés par nom de transporteur (non paginés). */
    rows: Record<string, Shipping[]>;
    transport: Transport[];
    /** Tranches de poids : valeur = id (chaîne). */
    poids: Option[];
}>();

const { filters, resetFilters } = useFilter({ search: '' }, shippingIndex);

const groups = computed(() => Object.entries(props.rows));
const transportOptions = computed<Option[]>(() =>
    props.transport.map((row) => ({ label: row.nom, value: String(row.id) })),
);

const creating = ref(false);

const form = useForm({
    transport_id: '',
    zone_id: '',
    poids_id: '',
    temps: '',
    montant: '',
});

const { zones, loading: zonesLoading, failed: zonesFailed, load: loadZones, clear: clearZones } = useTransportZones();

// Liste dépendante : une zone choisie pour l'ancien transporteur n'est pas forcément desservie par le nouveau.
watch(
    () => form.transport_id,
    (transportId) => {
        form.zone_id = '';
        form.clearErrors('zone_id');
        void loadZones(transportId);
    },
);

watch(creating, (open) => {
    if (!open) {
        form.reset();
        form.clearErrors();
        clearZones();
    }
});

const submit = (): void => {
    form.submit(store(), {
        onSuccess: () => {
            creating.value = false;
            toast.success('Tarif de livraison ajouté.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2 title="Livraisons" description="Les tarifs de livraison par transporteur, zone et tranche de poids.">
        <template #actions>
            <Button @click="creating = true">
                <Plus />
                Nouveau tarif
            </Button>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Rechercher un tarif ou un transporteur…"
                    aria-label="Rechercher un tarif de livraison"
                    class="pl-9"
                />
            </div>
            <Button v-if="filters.search" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <div class="overflow-x-auto rounded-lg border bg-card shadow-sm">
            <Table>
                <TableHeader class="bg-muted/50 [&_th]:h-10 [&_th]:text-xs [&_th]:font-semibold [&_th]:text-muted-foreground">
                    <TableRow class="hover:bg-transparent">
                        <TableHead class="w-16">#</TableHead>
                        <TableHead>Zone</TableHead>
                        <TableHead>Poids</TableHead>
                        <TableHead>Délai</TableHead>
                        <TableHead class="text-right">Montant</TableHead>
                        <TableHead>Créé le</TableHead>
                        <TableHead class="w-24 text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-for="[transportName, items] in groups" :key="transportName">
                        <TableRow class="bg-muted/30 hover:bg-muted/30">
                            <TableCell colspan="7" class="py-2 text-sm font-semibold">
                                {{ transportName }}
                                <span class="ml-1 font-normal text-muted-foreground">
                                    · {{ items.length }} {{ items.length > 1 ? 'tarifs' : 'tarif' }}
                                </span>
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="item in items" :key="item.id">
                            <TableCell class="text-muted-foreground">{{ item.id }}</TableCell>
                            <TableCell class="font-medium">{{ item.zone?.nom }}</TableCell>
                            <TableCell class="whitespace-nowrap">{{ item.poids?.min }} à {{ item.poids?.max }} Kg</TableCell>
                            <TableCell>{{ item.temps }}</TableCell>
                            <TableCell class="text-right whitespace-nowrap tabular-nums">{{ formatXof(item.montant) }}</TableCell>
                            <TableCell class="whitespace-nowrap text-muted-foreground">{{ item.created_at }}</TableCell>
                            <TableCell>
                                <div class="flex justify-end gap-1">
                                    <Button
                                        as-child
                                        variant="ghost"
                                        size="icon-sm"
                                        class="text-muted-foreground"
                                        :aria-label="`Modifier le tarif ${transportName} n°${item.id}`"
                                    >
                                        <a :href="edit(item.id).url" @click.prevent="router.visit(edit(item.id))">
                                            <Pencil />
                                        </a>
                                    </Button>
                                    <ConfirmDelete
                                        :action="destroy(item.id)"
                                        :what="`le tarif ${transportName} n°${item.id}`"
                                        success-message="Tarif supprimé."
                                    />
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-if="groups.length === 0" class="hover:bg-transparent">
                        <TableCell colspan="7" class="py-14 text-center text-muted-foreground">
                            <template v-if="filters.search">Aucun tarif ne correspond à « {{ filters.search }} ».</template>
                            <div v-else class="flex flex-col items-center gap-3">
                                Aucun tarif de livraison pour le moment.
                                <Button size="sm" @click="creating = true">
                                    <Plus />
                                    Ajouter un tarif
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <CrudDialog
            v-model:open="creating"
            title="Nouveau tarif de livraison"
            submit-label="Ajouter le tarif"
            :processing="form.processing"
            @submit="submit"
        >
            <FormField label="Transporteur" :error="form.errors.transport_id" required v-slot="field">
                <Combobox
                    :id="field.id"
                    v-model="form.transport_id"
                    :options="transportOptions"
                    placeholder="Choisir un transporteur"
                    search-placeholder="Rechercher un transporteur…"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
            <FormField
                label="Zone"
                :error="form.errors.zone_id || (zonesFailed ? 'Impossible de charger les zones, réessayez.' : undefined)"
                :hint="form.transport_id === '' ? `Choisissez d'abord un transporteur.` : undefined"
                required
                v-slot="field"
            >
                <Combobox
                    :id="field.id"
                    v-model="form.zone_id"
                    :options="zones"
                    :disabled="form.transport_id === '' || zonesLoading"
                    :placeholder="zonesLoading ? 'Chargement…' : 'Choisir une zone'"
                    search-placeholder="Rechercher une zone…"
                    empty-text="Ce transporteur ne dessert aucune zone."
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
            <FormField label="Poids" :error="form.errors.poids_id" required v-slot="field">
                <Combobox
                    :id="field.id"
                    v-model="form.poids_id"
                    :options="poids"
                    placeholder="Choisir une tranche de poids"
                    search-placeholder="Rechercher une tranche…"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
            <FormField label="Délai de livraison" :error="form.errors.temps" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.temps"
                    placeholder="Ex. 3 à 5 jours"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Montant (CFA)" :error="form.errors.montant" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.montant"
                    type="number"
                    min="0"
                    step="1"
                    inputmode="numeric"
                    placeholder="Prix du transport"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
        </CrudDialog>
    </AdminLayoutV2>
</template>

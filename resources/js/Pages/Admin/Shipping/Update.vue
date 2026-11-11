<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { computed, onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';
import { shipping as shippingIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/ShippingController';
import Combobox from '@/Components/Admin/Combobox.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { useTransportZones } from '@/Composables/useTransportZones';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Option, Shipping, Transport } from '@/types';

const props = defineProps<{
    shipping: Shipping;
    transport: Transport[];
    /** Tranches de poids : valeur = id (chaîne). */
    poids: Option[];
}>();

const transportOptions = computed<Option[]>(() =>
    props.transport.map((row) => ({ label: row.nom, value: String(row.id) })),
);

const form = useForm({
    transport_id: String(props.shipping.transport_id),
    zone_id: String(props.shipping.zone_id),
    poids_id: String(props.shipping.poids_id),
    temps: props.shipping.temps,
    montant: String(props.shipping.montant),
});

const { zones, loading: zonesLoading, failed: zonesFailed, load: loadZones } = useTransportZones();

onMounted(() => {
    void loadZones(form.transport_id);
});

// Liste dépendante : une zone choisie pour l'ancien transporteur n'est pas forcément desservie par le nouveau.
watch(
    () => form.transport_id,
    (transportId) => {
        form.zone_id = '';
        form.clearErrors('zone_id');
        void loadZones(transportId);
    },
);

const submit = (): void => {
    form.submit(update(props.shipping.id), {
        onSuccess: () => {
            form.defaults();
            toast.success('Tarif de livraison mis à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2
        :title="`Modifier le tarif n°${shipping.id}`"
        description="Changez le transporteur, la zone, la tranche de poids, le délai ou le montant."
    >
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="shippingIndex()">
                    <ArrowLeft />
                    Retour aux livraisons
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
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
                required
                v-slot="field"
            >
                <Combobox
                    :id="field.id"
                    v-model="form.zone_id"
                    :options="zones"
                    :disabled="zonesLoading"
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
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="shippingIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

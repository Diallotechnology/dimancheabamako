<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { transport as transportIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/TransportController';
import Combobox from '@/Components/Admin/Combobox.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Option, Transport } from '@/types';

const props = defineProps<{
    transport: Transport;
    /** Zones de livraison : valeur = id (chaîne). */
    zone: Option[];
}>();

const form = useForm<{ nom: string; zone_id: string[] }>({
    nom: props.transport.nom,
    zone_id: (props.transport.zones ?? []).map((zone) => String(zone.id)),
});

const submit = (): void => {
    form.submit(update(props.transport.id), {
        onSuccess: () => {
            form.defaults();
            toast.success('Transporteur mis à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2
        :title="`Modifier ${transport.nom}`"
        description="Renommez le transporteur ou changez les zones qu'il dessert."
    >
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="transportIndex()">
                    <ArrowLeft />
                    Retour aux transporteurs
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
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
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="transportIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

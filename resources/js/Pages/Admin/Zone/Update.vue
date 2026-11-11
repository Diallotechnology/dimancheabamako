<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { zone as zoneIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/ZoneController';
import Combobox from '@/Components/Admin/Combobox.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Option, Zone } from '@/types';

const props = defineProps<{
    zone: Zone;
    /** Noms de pays (valeur = libellé). */
    pays: Option[];
}>();

const form = useForm<{ nom: string; pays: string[] }>({
    nom: props.zone.nom,
    pays: (props.zone.countries ?? []).map((country) => country.nom),
});

const submit = (): void => {
    form.submit(update(props.zone.id), {
        onSuccess: () => {
            form.defaults();
            toast.success('Zone mise à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2 :title="`Modifier ${zone.nom}`" description="Renommez la zone ou changez les pays qu'elle regroupe.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="zoneIndex()">
                    <ArrowLeft />
                    Retour aux zones
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <FormField label="Nom" :error="form.errors.nom" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.nom"
                    placeholder="Nom de la zone"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Pays de livraison" :error="form.errors.pays" required v-slot="field">
                <Combobox
                    :id="field.id"
                    v-model="form.pays"
                    :options="pays"
                    multiple
                    placeholder="Choisir des pays"
                    search-placeholder="Rechercher un pays…"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="zoneIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { country as countryIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/CountryController';
import Combobox from '@/Components/Admin/Combobox.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Country, Option } from '@/types';

const props = defineProps<{
    country: Country;
    /** Noms de pays (valeur = libellé). */
    pays: Option[];
    zone: Option[];
}>();

const form = useForm<{ nom: string | null; zone_id: string | null }>({
    nom: props.country.nom,
    zone_id: String(props.country.zone_id),
});

const submit = (): void => {
    form.submit(update(props.country.id), {
        onSuccess: () => {
            form.defaults();
            toast.success('Pays mis à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2 :title="`Modifier ${country.nom}`" description="Changez le pays ou la zone tarifaire à laquelle il est rattaché.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="countryIndex()">
                    <ArrowLeft />
                    Retour aux pays
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <FormField label="Nom du pays" :error="form.errors.nom" required v-slot="field">
                <Combobox
                    :id="field.id"
                    v-model="form.nom"
                    :options="pays"
                    placeholder="Choisir un pays"
                    search-placeholder="Rechercher un pays…"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
            <FormField label="Zone" :error="form.errors.zone_id" required v-slot="field">
                <Combobox
                    :id="field.id"
                    v-model="form.zone_id"
                    :options="zone"
                    placeholder="Choisir une zone"
                    search-placeholder="Rechercher une zone…"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="countryIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

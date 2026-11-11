<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { client as clientIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/ClientController';
import Combobox from '@/Components/Admin/Combobox.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Client, Option } from '@/types';

const props = defineProps<{
    client: Client;
    country: Option[];
}>();

const form = useForm<{ prenom: string; nom: string; email: string; contact: string; pays: string | null }>({
    prenom: props.client.prenom,
    nom: props.client.nom,
    email: props.client.email,
    contact: props.client.contact,
    pays: props.client.pays,
});

const submit = (): void => {
    form.submit(update(props.client.id), {
        onSuccess: () => {
            form.defaults();
            toast.success('Client mis à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2
        :title="`Modifier ${client.prenom} ${client.nom}`"
        description="Mettez à jour les coordonnées du client."
    >
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="clientIndex()">
                    <ArrowLeft />
                    Retour aux clients
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <div class="grid gap-5 sm:grid-cols-2">
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
                        :aria-invalid="field.invalid"
                        :aria-describedby="field.describedBy"
                    />
                </FormField>
                <FormField label="Contact" :error="form.errors.contact" required v-slot="field">
                    <Input
                        :id="field.id"
                        v-model="form.contact"
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
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="clientIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

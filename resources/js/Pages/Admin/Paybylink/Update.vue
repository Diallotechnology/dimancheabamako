<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { paylink as paylinkIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/PayLinkController';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { PayLink } from '@/types';

const props = defineProps<{
    paylink: PayLink;
}>();

const form = useForm({
    name: props.paylink.name,
    contact: props.paylink.contact,
    montant: String(props.paylink.montant),
});

const submit = (): void => {
    form.submit(update(props.paylink.id), {
        onSuccess: () => {
            form.defaults();
            toast.success('Lien de paiement mis à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2
        :title="`Modifier le lien de ${paylink.name}`"
        description="Modifier le montant génère un nouveau lien de paiement."
    >
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="paylinkIndex()">
                    <ArrowLeft />
                    Retour aux liens
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <FormField label="Nom du client" :error="form.errors.name" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.name"
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
            <FormField label="Montant (CFA)" :error="form.errors.montant" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.montant"
                    type="number"
                    min="1"
                    step="1"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="paylinkIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

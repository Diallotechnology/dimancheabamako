<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { devise as deviseIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/DeviseController';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Devise } from '@/types';

const props = defineProps<{
    devise: Devise;
}>();

const form = useForm({
    taux: props.devise.taux,
});

const submit = (): void => {
    form.submit(update(props.devise.id), {
        onSuccess: () => {
            form.defaults();
            toast.success('Taux de change mis à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2 :title="`Modifier le taux ${devise.type}`" description="Le taux est un nombre entier, appliqué à la conversion des prix.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="deviseIndex()">
                    <ArrowLeft />
                    Retour aux devises
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <FormField label="Taux de change" :error="form.errors.taux" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.taux"
                    type="number"
                    step="1"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="deviseIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

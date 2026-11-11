<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { poids as poidsIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/PoidsController';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Poids } from '@/types';

const props = defineProps<{
    poid: Poids;
}>();

// Champs en texte : la règle serveur `decimal:2` exige la chaîne « 5.00 ».
const form = useForm({
    min: String(props.poid.min),
    max: String(props.poid.max),
});

const submit = (): void => {
    form.submit(update(props.poid.id), {
        onSuccess: () => {
            form.defaults();
            toast.success('Tranche de poids mise à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2
        :title="`Modifier la tranche ${poid.min} – ${poid.max} kg`"
        description="Mettez à jour les bornes de la tranche de poids."
    >
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="poidsIndex()">
                    <ArrowLeft />
                    Retour aux poids
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <FormField label="Minimum (kg)" hint="Avec deux décimales, par exemple 0.50." :error="form.errors.min" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.min"
                    inputmode="decimal"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Maximum (kg)" hint="Avec deux décimales, par exemple 5.00." :error="form.errors.max" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.max"
                    inputmode="decimal"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="poidsIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

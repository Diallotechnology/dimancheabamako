<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { category as categoryIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/CategoryController';
import FormField from '@/Components/Admin/FormField.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Category } from '@/types';

const props = defineProps<{
    category: Category;
}>();

const form = useForm({
    nom: props.category.nom,
    description: props.category.description,
});

const submit = (): void => {
    form.submit(update(props.category.id), {
        onSuccess: () => {
            // Les valeurs enregistrées deviennent la référence du formulaire.
            form.defaults();
            toast.success('Catégorie mise à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2 :title="`Modifier « ${category.nom} »`" description="Mettez à jour le nom et la description de la catégorie.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="categoryIndex()">
                    <ArrowLeft />
                    Retour aux catégories
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <FormField label="Nom" :error="form.errors.nom" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.nom"
                    placeholder="Le nom de la catégorie"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Description" :error="form.errors.description" required v-slot="field">
                <Textarea
                    :id="field.id"
                    v-model="form.description"
                    rows="4"
                    placeholder="La description courte de la catégorie"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="categoryIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

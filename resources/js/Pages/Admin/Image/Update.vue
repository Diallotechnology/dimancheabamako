<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { update } from '@/actions/App/Http/Controllers/ImageController';
import { show as showProduct } from '@/actions/App/Http/Controllers/ProductController';
import FormField from '@/Components/Admin/FormField.vue';
import ImageUpload from '@/Components/Admin/ImageUpload.vue';
import { Button } from '@/Components/ui/button';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Image } from '@/types';

const props = defineProps<{
    image: Image;
}>();

// PHP ne lit pas un multipart envoyé en PUT : envoi en POST, `_method` indique PUT à Laravel.
const form = useForm({
    chemin: null as File | null,
    _method: 'PUT' as const,
});

const submit = (): void => {
    form.post(update(props.image.id).url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('chemin');
            toast.success('Image remplacée.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2 title="Remplacer l'image" description="La nouvelle image remplace l'actuelle, qui est supprimée.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="showProduct(image.product_id)">
                    <ArrowLeft />
                    Retour au produit
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <FormField label="Nouvelle image" hint="JPG, PNG ou WebP, 5 Mo maximum." :error="form.errors.chemin" required v-slot="field">
                <ImageUpload
                    :id="field.id"
                    v-model="form.chemin"
                    :current-url="image.chemin"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                    :progress="form.progress?.percentage"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="showProduct(image.product_id)">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || form.chemin === null">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Remplacer l'image
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

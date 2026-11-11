<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { slide as slideIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/SlideController';
import FormField from '@/Components/Admin/FormField.vue';
import ImageUpload from '@/Components/Admin/ImageUpload.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Slide } from '@/types';

const props = defineProps<{
    slide: Slide;
}>();

// PHP ne lit pas un multipart envoyé en PUT : envoi en POST, `_method` indique PUT à Laravel.
const form = useForm({
    text_one: props.slide.text_one,
    text_two: props.slide.text_two,
    paragraph: props.slide.paragraph,
    image: null as File | null,
    _method: 'PUT' as const,
});

const submit = (): void => {
    form.post(update(props.slide.id).url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.defaults();
            form.reset('image');
            toast.success('Slide mis à jour.');
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2 :title="`Modifier « ${slide.text_one} »`" description="Les trois textes du slide et son image. Sans nouvelle image, l'actuelle est conservée.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="slideIndex()">
                    <ArrowLeft />
                    Retour aux slides
                </Link>
            </Button>
        </template>

        <form class="grid max-w-2xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <FormField label="Texte 1" :error="form.errors.text_one" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.text_one"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Texte 2" :error="form.errors.text_two" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.text_two"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Paragraphe" :error="form.errors.paragraph" required v-slot="field">
                <Input
                    :id="field.id"
                    v-model="form.paragraph"
                    :aria-invalid="field.invalid"
                    :aria-describedby="field.describedBy"
                />
            </FormField>
            <FormField label="Image du slide" hint="JPG, PNG ou WebP, 5 Mo maximum." :error="form.errors.image" v-slot="field">
                <ImageUpload
                    :id="field.id"
                    v-model="form.image"
                    :current-url="slide.image"
                    :invalid="field.invalid"
                    :described-by="field.describedBy"
                    :progress="form.progress?.percentage"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="slideIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

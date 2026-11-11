<script setup lang="ts">
import { ImagePlus, X } from '@lucide/vue';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';

/**
 * Champ d'envoi d'image : sélection, aperçu avant envoi et contrôle de taille côté client.
 * Il ne gère que le fichier (`v-model` : `File | null`) ; l'envoi reste à la page, qui doit
 * poster en multipart. La validation serveur (type, taille) reste l'autorité.
 *
 * Pattern d'envoi, à reprendre tel quel dans les autres pages :
 *
 *   const form = useForm({ image: null as File | null, _method: 'PUT' as const });
 *   // PHP ne lit pas un multipart en PUT : on poste en POST et `_method` l'indique à Laravel.
 *   form.post(update(id).url, { forceFormData: true, onSuccess: () => form.reset('image') });
 *
 *   <FormField label="Image" :error="form.errors.image" v-slot="field">
 *       <ImageUpload :id="field.id" v-model="form.image" :current-url="slide.image" :invalid="field.invalid"
 *           :progress="form.progress?.percentage" />
 *   </FormField>
 *
 * Le fichier est réinitialisé par `form.reset('image')` : le composant le détecte (modèle à null).
 */
const props = withDefaults(
    defineProps<{
        id?: string;
        /** Image actuellement enregistrée, affichée tant qu'aucun nouveau fichier n'est choisi. */
        currentUrl?: string | null;
        accept?: string;
        /** Taille maximale en Mo, alignée sur la règle `max:` du serveur (en Ko). */
        maxSizeMb?: number;
        invalid?: boolean;
        describedBy?: string;
        /** Avancement de l'envoi, de 0 à 100 (`form.progress?.percentage`). */
        progress?: number;
    }>(),
    {
        id: undefined,
        currentUrl: null,
        accept: 'image/jpeg,image/png,image/webp',
        maxSizeMb: 5,
        invalid: false,
        describedBy: undefined,
        progress: undefined,
    },
);

const file = defineModel<File | null>({ default: null });
const input = ref<InstanceType<typeof Input> | null>(null);
const sizeError = ref<string | null>(null);
const previewUrl = ref<string | null>(null);

const shownUrl = computed(() => previewUrl.value ?? props.currentUrl);

const revoke = (): void => {
    if (previewUrl.value !== null) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
};

// L'aperçu suit le modèle : un `form.reset()` de la page efface aussi l'aperçu et le champ.
watch(
    file,
    (value) => {
        revoke();

        if (value === null) {
            const element = (input.value?.$el ?? null) as HTMLInputElement | null;

            if (element) {
                element.value = '';
            }

            return;
        }

        previewUrl.value = URL.createObjectURL(value);
    },
    { immediate: true },
);

onBeforeUnmount(revoke);

const onChange = (event: Event): void => {
    const selected = (event.target as HTMLInputElement).files?.[0] ?? null;
    sizeError.value = null;

    if (selected !== null && selected.size > props.maxSizeMb * 1024 * 1024) {
        sizeError.value = `L'image dépasse ${props.maxSizeMb} Mo.`;
        (event.target as HTMLInputElement).value = '';
        file.value = null;

        return;
    }

    file.value = selected;
};

const clear = (): void => {
    sizeError.value = null;
    file.value = null;
};
</script>

<template>
    <div class="grid gap-3">
        <div
            class="flex aspect-video w-full max-w-sm items-center justify-center overflow-hidden rounded-md border bg-muted/40"
        >
            <img v-if="shownUrl" :src="shownUrl" alt="Aperçu de l'image" class="size-full object-contain" />
            <ImagePlus v-else class="size-8 text-muted-foreground" aria-hidden="true" />
        </div>
        <div class="flex items-center gap-2">
            <Input
                :id="id"
                ref="input"
                type="file"
                :accept="accept"
                :aria-invalid="invalid || sizeError !== null || undefined"
                :aria-describedby="describedBy"
                class="max-w-sm"
                @change="onChange"
            />
            <Button v-if="file" type="button" variant="ghost" size="icon-sm" aria-label="Retirer le fichier choisi" @click="clear">
                <X />
            </Button>
        </div>
        <p v-if="sizeError" class="text-xs font-medium text-destructive" role="alert">{{ sizeError }}</p>
        <progress v-if="progress !== undefined" :value="progress" max="100" class="h-1.5 w-full max-w-sm accent-primary">
            {{ progress }} %
        </progress>
    </div>
</template>

<script setup lang="ts">
import { X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';

/**
 * Champ d'envoi d'une vidéo facultative (`v-model` : `File | null`) avec contrôle de taille côté client.
 * Pas d'aperçu local : seule la vidéo actuellement enregistrée (`current-url`) est lisible. L'envoi
 * reste à la page, en multipart ; la validation serveur (type, taille) reste l'autorité.
 */
const props = withDefaults(
    defineProps<{
        id?: string;
        /** Vidéo actuellement enregistrée, lisible tant qu'aucun nouveau fichier n'est choisi. */
        currentUrl?: string | null;
        accept?: string;
        /** Taille maximale en Mo, alignée sur la règle `max:` du serveur (en Ko). */
        maxSizeMb?: number;
        invalid?: boolean;
        describedBy?: string;
        progress?: number;
    }>(),
    {
        id: undefined,
        currentUrl: null,
        accept: 'video/mp4,video/quicktime,video/webm',
        maxSizeMb: 50,
        invalid: false,
        describedBy: undefined,
        progress: undefined,
    },
);

const file = defineModel<File | null>({ default: null });
const input = ref<InstanceType<typeof Input> | null>(null);
const sizeError = ref<string | null>(null);

// Un `form.reset('video')` de la page vide aussi le champ.
watch(file, (value) => {
    if (value === null) {
        const element = (input.value?.$el ?? null) as HTMLInputElement | null;

        if (element) {
            element.value = '';
        }
    }
});

const onChange = (event: Event): void => {
    const selected = (event.target as HTMLInputElement).files?.[0] ?? null;
    sizeError.value = null;

    if (selected !== null && selected.size > props.maxSizeMb * 1024 * 1024) {
        sizeError.value = `La vidéo dépasse ${props.maxSizeMb} Mo.`;
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
        <video v-if="currentUrl && file === null" :src="currentUrl" controls class="max-h-48 w-full max-w-sm rounded-md border" />
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
            <Button v-if="file" type="button" variant="ghost" size="icon-sm" aria-label="Retirer la vidéo choisie" @click="clear">
                <X />
            </Button>
        </div>
        <p v-if="sizeError" class="text-xs font-medium text-destructive" role="alert">{{ sizeError }}</p>
        <progress v-if="progress !== undefined" :value="progress" max="100" class="h-1.5 w-full max-w-sm accent-primary">
            {{ progress }} %
        </progress>
    </div>
</template>

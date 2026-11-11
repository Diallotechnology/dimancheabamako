<script setup lang="ts">
import { ImagePlus, X } from '@lucide/vue';
import { onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';

/**
 * Envoi de plusieurs images : aperçus avant envoi, retrait fichier par fichier et contrôle de taille
 * côté client. Les sélections successives s'ajoutent les unes aux autres. Version multiple de
 * `ImageUpload` (`v-model` : `File[]`) ; la validation serveur (type, taille) reste l'autorité.
 * L'envoi reste à la page, en multipart (`forceFormData`).
 */
const props = withDefaults(
    defineProps<{
        id?: string;
        accept?: string;
        /** Taille maximale par image en Mo, alignée sur la règle `max:` du serveur (en Ko). */
        maxSizeMb?: number;
        invalid?: boolean;
        describedBy?: string;
        /** Avancement de l'envoi, de 0 à 100 (`form.progress?.percentage`). */
        progress?: number;
    }>(),
    {
        id: undefined,
        accept: 'image/jpeg,image/png,image/webp',
        maxSizeMb: 5,
        invalid: false,
        describedBy: undefined,
        progress: undefined,
    },
);

const files = defineModel<File[]>({ default: () => [] });
const input = ref<InstanceType<typeof Input> | null>(null);
const rejected = ref<string[]>([]);
const previews = ref<{ file: File; url: string }[]>([]);

const revokeAll = (): void => {
    previews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
    previews.value = [];
};

// Les aperçus suivent le modèle : un `form.reset('image')` de la page efface aussi les aperçus et le champ.
watch(
    files,
    (value) => {
        const kept = previews.value.filter((preview) => value.includes(preview.file));
        previews.value
            .filter((preview) => !value.includes(preview.file))
            .forEach((preview) => URL.revokeObjectURL(preview.url));

        previews.value = value.map(
            (file) => kept.find((preview) => preview.file === file) ?? { file, url: URL.createObjectURL(file) },
        );

        if (value.length === 0) {
            const element = (input.value?.$el ?? null) as HTMLInputElement | null;

            if (element) {
                element.value = '';
            }
        }
    },
    { immediate: true, deep: true },
);

onBeforeUnmount(revokeAll);

const onChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    const selected = Array.from(target.files ?? []);
    const limit = props.maxSizeMb * 1024 * 1024;

    rejected.value = selected.filter((file) => file.size > limit).map((file) => file.name);
    files.value = [...files.value, ...selected.filter((file) => file.size <= limit)];

    // Le champ ne sert que de sélecteur : les fichiers retenus vivent dans le modèle.
    target.value = '';
};

const remove = (file: File): void => {
    files.value = files.value.filter((candidate) => candidate !== file);
};
</script>

<template>
    <div class="grid gap-3">
        <Input
            :id="id"
            ref="input"
            type="file"
            multiple
            :accept="accept"
            :aria-invalid="invalid || rejected.length > 0 || undefined"
            :aria-describedby="describedBy"
            class="max-w-sm"
            @change="onChange"
        />
        <p v-if="rejected.length > 0" class="text-xs font-medium text-destructive" role="alert">
            Dépasse {{ maxSizeMb }} Mo, non ajouté : {{ rejected.join(', ') }}.
        </p>
        <ul v-if="previews.length > 0" class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-6">
            <li
                v-for="preview in previews"
                :key="preview.url"
                class="relative aspect-square overflow-hidden rounded-md border bg-muted/40"
            >
                <img :src="preview.url" :alt="preview.file.name" class="size-full object-cover" />
                <Button
                    type="button"
                    variant="secondary"
                    size="icon-sm"
                    class="absolute top-1 right-1 size-6"
                    :aria-label="`Retirer ${preview.file.name}`"
                    @click="remove(preview.file)"
                >
                    <X />
                </Button>
            </li>
        </ul>
        <p v-else class="flex items-center gap-2 text-xs text-muted-foreground">
            <ImagePlus class="size-4" aria-hidden="true" />
            Aucune image choisie.
        </p>
        <progress v-if="progress !== undefined" :value="progress" max="100" class="h-1.5 w-full max-w-sm accent-primary">
            {{ progress }} %
        </progress>
    </div>
</template>

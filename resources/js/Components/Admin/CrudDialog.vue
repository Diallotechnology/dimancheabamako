<script setup lang="ts">
import { Loader2 } from '@lucide/vue';
import { useId } from 'vue';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

/**
 * Formulaire de création/édition en modale (remplace Modal.vue + la fermeture via #exampleModal).
 * L'ouverture est pilotée par `v-model:open` : la page ferme la modale dans `onSuccess`,
 * jamais en cas d'erreur de validation, pour que l'utilisateur voie ses erreurs.
 *
 *   <CrudDialog v-model:open="creating" title="Nouvelle catégorie" submit-label="Créer la catégorie"
 *       :processing="form.processing" @submit="submit">…champs…</CrudDialog>
 */
withDefaults(
    defineProps<{
        title: string;
        description?: string;
        submitLabel: string;
        processing?: boolean;
        /** Largeur : `md` pour 1 colonne, `lg` pour un formulaire en 2 colonnes. */
        size?: 'md' | 'lg';
    }>(),
    {
        description: undefined,
        processing: false,
        size: 'md',
    },
);

const open = defineModel<boolean>('open', { default: false });
const formId = useId();

defineEmits<{ submit: [] }>();
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            :class="size === 'lg' ? 'sm:max-w-2xl' : 'sm:max-w-lg'"
            class="max-h-[calc(100dvh-2rem)] grid-rows-[auto_minmax(0,1fr)_auto] overflow-hidden p-0"
        >
            <DialogHeader class="border-b px-6 pt-6 pb-4">
                <DialogTitle class="font-display text-lg">{{ title }}</DialogTitle>
                <DialogDescription v-if="description">{{ description }}</DialogDescription>
            </DialogHeader>
            <form
                :id="formId"
                class="grid content-start gap-4 overflow-y-auto px-6 py-5"
                novalidate
                @submit.prevent="$emit('submit')"
            >
                <slot />
            </form>
            <DialogFooter class="border-t bg-muted/40 px-6 py-4">
                <Button type="button" variant="outline" :disabled="processing" @click="open = false">
                    Annuler
                </Button>
                <Button type="submit" :form="formId" :disabled="processing">
                    <Loader2 v-if="processing" class="animate-spin" />
                    {{ submitLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

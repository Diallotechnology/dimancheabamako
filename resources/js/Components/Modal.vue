<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/Components/ui/dialog';

/**
 * Pont temporaire pour les pages non migrées : elles ouvrent la modale avec
 * `data-bs-toggle="modal"` et la ferment avec `data-bs-dismiss="modal"` ou notify().
 * Bootstrap n'étant plus chargé, ces attributs sont interprétés ici. Supprimé au nettoyage final.
 */
defineProps<{ name: string }>();

// Même nom d'événement que hidemodal() dans helper.js.
const LEGACY_MODAL_CLOSE = 'legacy-modal:close';

const open = ref(false);

const onClick = (event: MouseEvent): void => {
    const target = event.target as HTMLElement | null;

    if (target?.closest('[data-bs-toggle="modal"]')) {
        event.preventDefault();
        open.value = true;
    } else if (target?.closest('[data-bs-dismiss="modal"]')) {
        event.preventDefault();
        open.value = false;
    }
};

const close = (): void => {
    open.value = false;
};

onMounted(() => {
    document.addEventListener('click', onClick);
    window.addEventListener(LEGACY_MODAL_CLOSE, close);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onClick);
    window.removeEventListener(LEGACY_MODAL_CLOSE, close);
});
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="max-h-[calc(100dvh-2rem)] overflow-y-auto sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="font-display text-lg">{{ name }}</DialogTitle>
                <DialogDescription class="sr-only">{{ name }}</DialogDescription>
            </DialogHeader>
            <slot />
        </DialogContent>
    </Dialog>
</template>

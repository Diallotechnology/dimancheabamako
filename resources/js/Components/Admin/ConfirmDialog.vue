<script setup lang="ts">
import { Loader2 } from '@lucide/vue';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import { Button } from '@/Components/ui/button';

/**
 * Confirmation d'une action à conséquence. Le bouton de confirmation n'est pas un
 * AlertDialogAction : la boîte reste ouverte pendant la requête (`busy`) et c'est
 * l'appelant qui la ferme une fois l'action réussie.
 */
withDefaults(
    defineProps<{
        title: string;
        description?: string;
        confirmLabel: string;
        cancelLabel?: string;
        tone?: 'default' | 'destructive';
        busy?: boolean;
    }>(),
    {
        description: undefined,
        cancelLabel: 'Annuler',
        tone: 'default',
        busy: false,
    },
);

const open = defineModel<boolean>('open', { default: false });

defineEmits<{ confirm: [] }>();
</script>

<template>
    <AlertDialog v-model:open="open">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle class="font-display text-lg">{{ title }}</AlertDialogTitle>
                <AlertDialogDescription v-if="description || $slots.default">
                    <slot>{{ description }}</slot>
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel :disabled="busy">{{ cancelLabel }}</AlertDialogCancel>
                <Button :variant="tone" :disabled="busy" @click="$emit('confirm')">
                    <Loader2 v-if="busy" class="animate-spin" />
                    {{ confirmLabel }}
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>

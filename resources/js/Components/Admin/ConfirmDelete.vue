<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog.vue';
import { Button } from '@/Components/ui/button';
import type { RouteDefinition } from '@/wayfinder';

/**
 * Suppression confirmée (remplace ButtonDelete + SweetAlert2).
 * Les contrôleurs répondent en JSON (DeleteAction::supp) ou sans contenu (Order) : tout
 * statut 2xx vaut succès, puis les props de la page sont rechargées.
 */
const props = defineProps<{
    action: RouteDefinition<'delete'>;
    /** Ce qui disparaît, avec son article : « la catégorie Bogolan ». */
    what: string;
    /** Conséquence au-delà de la ligne supprimée, si elle existe. */
    consequence?: string;
    /** Toast de succès : « Catégorie supprimée. » */
    successMessage?: string;
}>();

const emit = defineEmits<{ deleted: [] }>();

const open = ref(false);
const busy = ref(false);

const destroy = async (): Promise<void> => {
    busy.value = true;

    try {
        await window.axios.delete(props.action.url);
        open.value = false;
        toast.success(props.successMessage ?? 'Suppression effectuée.');
        emit('deleted');
        router.reload();
    } catch (error: unknown) {
        const status = (error as { response?: { status?: number } }).response?.status;
        toast.error(
            status === 404
                ? 'Élément introuvable : il a peut-être déjà été supprimé.'
                : 'La suppression a échoué. Réessayez ou rechargez la page.',
        );
    } finally {
        busy.value = false;
    }
};
</script>

<template>
    <Button
        variant="ghost"
        size="icon-sm"
        class="text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
        :aria-label="`Supprimer ${what}`"
        @click="open = true"
    >
        <Trash2 />
    </Button>
    <ConfirmDialog
        v-model:open="open"
        :title="`Supprimer ${what} ?`"
        :description="consequence ?? 'Cette action est définitive.'"
        confirm-label="Supprimer"
        tone="destructive"
        :busy="busy"
        @confirm="destroy"
    />
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ImageOff, Pencil } from '@lucide/vue';
import { edit, destroy } from '@/actions/App/Http/Controllers/SlideController';
import ConfirmDelete from '@/Components/Admin/ConfirmDelete.vue';
import { Button } from '@/Components/ui/button';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Slide } from '@/types';

defineProps<{
    rows: Slide[];
}>();
</script>

<template>
    <AdminLayoutV2 title="Slides" description="Les bannières du carrousel de la page d'accueil.">
        <ul v-if="rows.length > 0" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <li v-for="row in rows" :key="row.id" class="flex flex-col overflow-hidden rounded-lg border bg-card shadow-sm">
                <div class="flex aspect-video items-center justify-center bg-muted/40">
                    <img v-if="row.image" :src="row.image" :alt="row.text_one" class="size-full object-cover" loading="lazy" />
                    <ImageOff v-else class="size-8 text-muted-foreground" aria-hidden="true" />
                </div>
                <div class="grid flex-1 gap-1 p-4">
                    <h2 class="font-semibold">{{ row.text_one }}</h2>
                    <p class="text-sm">{{ row.text_two }}</p>
                    <p class="text-sm text-muted-foreground">{{ row.paragraph }}</p>
                </div>
                <div class="flex justify-end gap-1 border-t px-3 py-2">
                    <Button
                        as-child
                        variant="ghost"
                        size="icon-sm"
                        class="text-muted-foreground"
                        :aria-label="`Modifier le slide ${row.text_one}`"
                    >
                        <a :href="edit(row.id).url" @click.prevent="router.visit(edit(row.id))">
                            <Pencil />
                        </a>
                    </Button>
                    <ConfirmDelete
                        :action="destroy(row.id)"
                        :what="`le slide ${row.text_one}`"
                        consequence="L'image du slide sera aussi supprimée."
                        success-message="Slide supprimé."
                    />
                </div>
            </li>
        </ul>
        <p v-else class="rounded-lg border bg-card py-14 text-center text-muted-foreground">Aucun slide pour le moment.</p>
    </AdminLayoutV2>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import { Separator } from '@/Components/ui/separator';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/Components/ui/sidebar';
import { Toaster } from '@/Components/ui/sonner';

/**
 * Layout du back-office. `title` alimente l'onglet et l'en-tête de page ;
 * le slot `actions` reçoit le bouton principal de la page (« Nouvelle catégorie »).
 * Sans `title` (pages non migrées), l'en-tête se réduit à la barre d'outils.
 */
defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <Head v-if="title" :title="title" />
    <SidebarProvider>
        <AdminSidebar />
        <SidebarInset class="min-w-0">
            <header
                class="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-2 border-b bg-background/90 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/75 md:px-6"
            >
                <SidebarTrigger class="-ml-1.5" aria-label="Afficher ou masquer le menu" />
                <Separator orientation="vertical" class="mr-1 data-[orientation=vertical]:h-5" />
                <span v-if="title" class="truncate text-sm text-muted-foreground">{{ title }}</span>
            </header>

            <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 md:px-6 md:py-8">
                <div
                    v-if="title"
                    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div class="min-w-0">
                        <h1 class="text-2xl font-semibold text-foreground md:text-[1.75rem]">{{ title }}</h1>
                        <p v-if="description" class="mt-1 max-w-[65ch] text-sm text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>
                    <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center gap-2">
                        <slot name="actions" />
                    </div>
                </div>
                <slot />
            </main>
        </SidebarInset>
        <Toaster rich-colors close-button />
    </SidebarProvider>
</template>

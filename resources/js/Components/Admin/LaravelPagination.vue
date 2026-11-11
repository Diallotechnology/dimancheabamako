<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/Components/ui/pagination';
import type { Paginated } from '@/types';

/**
 * Pagination d'un `->paginate()` Laravel. La page visée est posée sur l'URL courante :
 * les filtres de la query string sont conservés même sans `withQueryString()` côté serveur.
 */
const props = defineProps<{
    paginator: Paginated<unknown>;
    /** Nom de ce qui est listé, au pluriel : « catégories ». */
    itemLabel?: string;
}>();

const goTo = (page: number): void => {
    const url = new URL(window.location.href);
    url.searchParams.set('page', String(page));

    router.get(url.pathname + url.search, {}, { preserveState: true });
};
</script>

<template>
    <div
        v-if="paginator.total > 0"
        class="flex flex-col items-center justify-between gap-3 border-t px-4 py-3 text-sm sm:flex-row"
    >
        <p class="text-muted-foreground">
            {{ paginator.from }}–{{ paginator.to }} sur {{ paginator.total }}
            <template v-if="itemLabel">{{ itemLabel }}</template>
        </p>
        <Pagination
            v-if="paginator.last_page > 1"
            :page="paginator.current_page"
            :total="paginator.total"
            :items-per-page="paginator.per_page"
            :sibling-count="1"
            show-edges
            class="mx-0 w-auto"
            @update:page="goTo"
        >
            <PaginationContent v-slot="{ items }">
                <PaginationPrevious size="sm" aria-label="Page précédente">
                    <ChevronLeft />
                </PaginationPrevious>
                <template v-for="(item, index) in items" :key="index">
                    <PaginationItem
                        v-if="item.type === 'page'"
                        :value="item.value"
                        :is-active="item.value === paginator.current_page"
                        size="icon-sm"
                        class="data-[selected=true]:border-primary data-[selected=true]:font-semibold"
                    >
                        {{ item.value }}
                    </PaginationItem>
                    <PaginationEllipsis v-else :index="index" />
                </template>
                <PaginationNext size="sm" aria-label="Page suivante">
                    <ChevronRight />
                </PaginationNext>
            </PaginationContent>
        </Pagination>
    </div>
</template>

<script setup lang="ts" generic="T extends { id: number | string }">
import { computed } from 'vue';
import LaravelPagination from '@/Components/Admin/LaravelPagination.vue';
import { Table, TableBody, TableCell, TableHeader, TableRow } from '@/Components/ui/table';
import type { Paginated } from '@/types';

/**
 * Tableau de liste admin. Accepte un paginateur Laravel (pagination affichée) ou un tableau simple
 * (listes non paginées : Slide, Shipping groupé).
 *
 *   <DataTable :rows="rows" item-label="catégories" :columns="4">
 *       <template #head><TableHead>Nom</TableHead>…</template>
 *       <template #row="{ row }"><TableCell>{{ row.nom }}</TableCell>…</template>
 *       <template #empty>Aucune catégorie. <Button>Créer une catégorie</Button></template>
 *   </DataTable>
 */
const props = defineProps<{
    rows: Paginated<T> | T[];
    /** Nombre de colonnes, pour que la ligne vide occupe toute la largeur. */
    columns: number;
    itemLabel?: string;
}>();

defineSlots<{
    head(): unknown;
    row(props: { row: T; index: number }): unknown;
    empty?(): unknown;
}>();

const data = computed<T[]>(() => (Array.isArray(props.rows) ? props.rows : props.rows.data));
const paginator = computed(() => (Array.isArray(props.rows) ? null : props.rows));
</script>

<template>
    <div class="overflow-hidden rounded-lg border bg-card shadow-sm">
        <Table>
            <TableHeader class="bg-muted/50 [&_th]:h-10 [&_th]:text-xs [&_th]:font-semibold [&_th]:text-muted-foreground">
                <TableRow class="hover:bg-transparent">
                    <slot name="head" />
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="(row, index) in data" :key="row.id">
                    <slot name="row" :row="row" :index="index" />
                </TableRow>
                <TableRow v-if="data.length === 0" class="hover:bg-transparent">
                    <TableCell :colspan="columns" class="py-14 text-center text-muted-foreground">
                        <slot name="empty">Aucun élément pour le moment.</slot>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
        <LaravelPagination v-if="paginator" :paginator="paginator" :item-label="itemLabel" />
    </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Pencil } from '@lucide/vue';
import { edit } from '@/actions/App/Http/Controllers/DeviseController';
import DataTable from '@/Components/Admin/DataTable.vue';
import { Button } from '@/Components/ui/button';
import { TableCell, TableHead } from '@/Components/ui/table';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Devise, Paginated } from '@/types';

defineProps<{
    rows: Paginated<Devise>;
}>();
</script>

<template>
    <AdminLayoutV2 title="Devises" description="Le taux de change appliqué aux prix affichés dans la boutique.">
        <DataTable :rows="rows" :columns="5" item-label="devises">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Devise</TableHead>
                <TableHead>Taux</TableHead>
                <TableHead>Créée le</TableHead>
                <TableHead class="w-24 text-right">Actions</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium">{{ row.type }}</TableCell>
                <TableCell>{{ row.taux }}</TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
                <TableCell>
                    <div class="flex justify-end">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Modifier le taux ${row.type}`"
                        >
                            <a :href="edit(row.id).url" @click.prevent="router.visit(edit(row.id))">
                                <Pencil />
                            </a>
                        </Button>
                    </div>
                </TableCell>
            </template>

            <template #empty>Aucune devise configurée.</template>
        </DataTable>
    </AdminLayoutV2>
</template>

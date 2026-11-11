<script setup lang="ts">
import { Search, X } from '@lucide/vue';
import { customer as customerIndex } from '@/actions/App/Http/Controllers/AdminController';
import DataTable from '@/Components/Admin/DataTable.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { TableCell, TableHead } from '@/Components/ui/table';
import { useFilter } from '@/Composables/useFilter';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Paginated, User } from '@/types';

defineProps<{
    rows: Paginated<User>;
}>();

const { filters, resetFilters } = useFilter({ search: '' }, customerIndex);
</script>

<template>
    <AdminLayoutV2 title="Comptes clients" description="Les comptes créés par les clients de la boutique (consultation seule).">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-sm">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Rechercher par nom ou e-mail…"
                    aria-label="Rechercher un compte client"
                    class="pl-9"
                />
            </div>
            <Button v-if="filters.search" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <DataTable :rows="rows" :columns="6" item-label="comptes">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Nom</TableHead>
                <TableHead>E-mail</TableHead>
                <TableHead>Rôle</TableHead>
                <TableHead>État</TableHead>
                <TableHead>Créé le</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium">{{ row.name }}</TableCell>
                <TableCell class="text-muted-foreground">{{ row.email }}</TableCell>
                <TableCell>{{ row.role }}</TableCell>
                <TableCell>
                    <Badge v-if="row.etat" variant="success" size="sm">En ligne</Badge>
                    <Badge v-else variant="outline" size="sm">Hors ligne</Badge>
                </TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
            </template>

            <template #empty>
                <template v-if="filters.search">Aucun compte ne correspond à « {{ filters.search }} ».</template>
                <template v-else>Aucun compte client pour le moment.</template>
            </template>
        </DataTable>
    </AdminLayoutV2>
</template>

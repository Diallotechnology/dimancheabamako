<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Banknote, Eye, Package, ShoppingBag, Tags } from '@lucide/vue';
import type { Component } from 'vue';
import { show as showOrder } from '@/actions/App/Http/Controllers/OrderController';
import DataTable from '@/Components/Admin/DataTable.vue';
import OrdersChart from '@/Components/Admin/OrdersChart.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { TableCell, TableHead } from '@/Components/ui/table';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import { formatXof } from '@/lib/format';
import type { Order } from '@/types';

const props = defineProps<{
    /** Commandes réglées (`PURCHASED`) du mois courant. */
    order: number;
    product: number;
    /** Somme des lignes des commandes réglées (`PURCHASED`) du mois courant : même base que `order`. */
    revenu: number;
    categorie: number;
    /** Les 10 dernières commandes, liste simple (pas de paginateur). */
    lastorder: Order[];
    /** Dates `YYYY-MM-DD` triées, parallèles à `data`. */
    label: string[];
    /** Nombre de commandes créées ce jour-là. */
    data: number[];
}>();

interface Kpi {
    title: string;
    value: string;
    icon: Component;
}

const kpis: Kpi[] = [
    { title: 'Revenu du mois', value: formatXof(props.revenu), icon: Banknote },
    { title: 'Ventes réglées du mois', value: String(props.order), icon: ShoppingBag },
    { title: 'Produits', value: String(props.product), icon: Package },
    { title: 'Catégories', value: String(props.categorie), icon: Tags },
];
</script>

<template>
    <AdminLayoutV2 title="Tableau de bord" description="Activité de la boutique : ventes, catalogue et commandes récentes.">
        <section class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Indicateurs clés">
            <Card v-for="kpi in kpis" :key="kpi.title" class="gap-3 py-5">
                <CardContent class="flex items-center gap-4">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary/15 text-foreground">
                        <component :is="kpi.icon" class="size-5" aria-hidden="true" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm text-muted-foreground">{{ kpi.title }}</p>
                        <p class="truncate text-xl font-semibold tabular-nums">{{ kpi.value }}</p>
                    </div>
                </CardContent>
            </Card>
        </section>

        <Card class="mb-8">
            <CardHeader>
                <CardTitle>Commandes par jour (toutes, payées ou non)</CardTitle>
            </CardHeader>
            <CardContent>
                <OrdersChart v-if="label.length > 0" :labels="label" :values="data" />
                <p v-else class="py-12 text-center text-sm text-muted-foreground">Aucune commande à afficher pour le moment.</p>
            </CardContent>
        </Card>

        <h2 class="mb-3 text-lg font-semibold">Ventes récentes</h2>
        <DataTable :rows="lastorder" :columns="8">
            <template #head>
                <TableHead>Référence</TableHead>
                <TableHead>Client</TableHead>
                <TableHead>Livraison</TableHead>
                <TableHead>Transaction</TableHead>
                <TableHead class="text-right">Total</TableHead>
                <TableHead>État</TableHead>
                <TableHead>Date</TableHead>
                <TableHead class="w-16 text-right">Voir</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="font-medium">{{ row.reference ?? `#${row.id}` }}</TableCell>
                <TableCell>
                    <div>{{ row.client?.nom }}</div>
                    <div class="text-xs text-muted-foreground">{{ row.client?.email }}</div>
                </TableCell>
                <TableCell>
                    <div>{{ row.adresse }}</div>
                    <div class="text-xs text-muted-foreground">
                        {{ [row.postal, row.ville, row.country?.nom].filter(Boolean).join(' · ') }}
                    </div>
                </TableCell>
                <TableCell>
                    <div>{{ row.trans_ref }}</div>
                    <div class="text-xs text-muted-foreground">{{ row.trans_state }}</div>
                </TableCell>
                <TableCell class="text-right tabular-nums">{{ formatXof(Number(row.totaux ?? 0)) }}</TableCell>
                <TableCell><Badge variant="outline" size="sm">{{ row.etat }}</Badge></TableCell>
                <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.created_at }}</TableCell>
                <TableCell>
                    <div class="flex justify-end">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Voir la commande ${row.reference ?? row.id}`"
                        >
                            <Link :href="showOrder(row.id)">
                                <Eye />
                            </Link>
                        </Button>
                    </div>
                </TableCell>
            </template>

            <template #empty>Aucune vente récente.</template>
        </DataTable>
    </AdminLayoutV2>
</template>

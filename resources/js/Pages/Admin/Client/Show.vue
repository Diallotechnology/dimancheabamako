<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Eye, Mail, MapPin, Phone } from '@lucide/vue';
import { client as clientIndex } from '@/actions/App/Http/Controllers/AdminController';
import { show as showOrder } from '@/actions/App/Http/Controllers/OrderController';
import DataTable from '@/Components/Admin/DataTable.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { TableCell, TableHead } from '@/Components/ui/table';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Client } from '@/types';

const props = defineProps<{
    client: Client;
}>();

const orders = props.client.orders ?? [];
</script>

<template>
    <AdminLayoutV2 :title="`${client.prenom} ${client.nom}`" description="Coordonnées et historique d'achats du client.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="clientIndex()">
                    <ArrowLeft />
                    Retour aux clients
                </Link>
            </Button>
        </template>

        <dl class="mb-8 grid gap-4 rounded-lg border bg-card p-6 text-sm shadow-sm sm:grid-cols-3">
            <div class="flex items-center gap-3">
                <Mail class="size-4 text-muted-foreground" aria-hidden="true" />
                <div>
                    <dt class="text-xs text-muted-foreground">E-mail</dt>
                    <dd class="font-medium break-all">{{ client.email }}</dd>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Phone class="size-4 text-muted-foreground" aria-hidden="true" />
                <div>
                    <dt class="text-xs text-muted-foreground">Contact</dt>
                    <dd class="font-medium">{{ client.contact }}</dd>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <MapPin class="size-4 text-muted-foreground" aria-hidden="true" />
                <div>
                    <dt class="text-xs text-muted-foreground">Pays</dt>
                    <dd class="font-medium">{{ client.pays }}</dd>
                </div>
            </div>
        </dl>

        <h2 class="mb-3 text-lg font-semibold">Achats</h2>
        <DataTable :rows="orders" :columns="9">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Référence</TableHead>
                <TableHead>Adresse</TableHead>
                <TableHead>Postal</TableHead>
                <TableHead>Pays</TableHead>
                <TableHead>Ville</TableHead>
                <TableHead>Paiement</TableHead>
                <TableHead>État</TableHead>
                <TableHead class="w-16 text-right">Voir</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium">{{ row.reference }}</TableCell>
                <TableCell>{{ row.adresse }}</TableCell>
                <TableCell>{{ row.postal }}</TableCell>
                <TableCell>{{ row.country?.nom }}</TableCell>
                <TableCell>{{ row.ville }}</TableCell>
                <TableCell>{{ row.trans_state }}</TableCell>
                <TableCell><Badge variant="outline" size="sm">{{ row.etat }}</Badge></TableCell>
                <TableCell>
                    <div class="flex justify-end">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Voir la commande ${row.reference ?? row.id}`"
                        >
                            <a :href="showOrder(row.id).url" @click.prevent="router.visit(showOrder(row.id))">
                                <Eye />
                            </a>
                        </Button>
                    </div>
                </TableCell>
            </template>

            <template #empty>Ce client n'a encore passé aucune commande.</template>
        </DataTable>
    </AdminLayoutV2>
</template>

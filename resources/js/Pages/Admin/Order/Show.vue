<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ImageOff } from '@lucide/vue';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { order as orderIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/OrderController';
import DataTable from '@/Components/Admin/DataTable.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { NativeSelect } from '@/Components/ui/native-select';
import { TableCell, TableHead } from '@/Components/ui/table';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import { formatXof } from '@/lib/format';
import type { Order, OrderState } from '@/types';

const props = defineProps<{
    order: Order;
    /** États possibles (`OrderEnum::cases()` sérialisé en valeurs). */
    state: OrderState[];
}>();

const form = useForm<{ etat: OrderState }>({
    etat: props.order.etat,
});

const submit = (): void => {
    form.submit(update(props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.defaults();
            toast.success('Vente mise à jour.');
        },
        onError: () => {
            toast.error("L'état n'a pas pu être modifié.");
        },
    });
};

const products = computed(() => props.order.products ?? []);

// Calculé : l'état affiché doit suivre la prop rechargée après « Valider ».
const infos = computed<{ title: string; rows: { label: string; value: string }[] }[]>(() => [
    {
        title: 'Client',
        rows: [
            { label: 'Nom', value: `${props.order.client?.prenom ?? ''} ${props.order.client?.nom ?? ''}`.trim() || '—' },
            { label: 'E-mail', value: props.order.client?.email ?? '—' },
            { label: 'Contact', value: props.order.client?.contact ?? '—' },
        ],
    },
    {
        title: 'Vente',
        rows: [
            { label: 'Adresse', value: props.order.adresse },
            { label: 'Livraison', value: formatXof(Number(props.order.shipping)) },
            { label: 'État', value: props.order.etat },
        ],
    },
    {
        title: 'Destination',
        rows: [
            { label: 'Pays', value: props.order.country?.nom ?? '—' },
            { label: 'Ville', value: props.order.ville },
            { label: 'Code postal', value: props.order.postal ?? '—' },
            { label: 'Poids', value: props.order.poids },
        ],
    },
]);
</script>

<template>
    <AdminLayoutV2
        :title="`Vente n°${order.id}`"
        :description="`Facture ${order.reference ?? '—'} · ${order.created_at}`"
    >
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="orderIndex()">
                    <ArrowLeft />
                    Retour aux ventes
                </Link>
            </Button>
        </template>

        <section class="mb-6 flex flex-wrap items-end justify-between gap-4 rounded-lg border bg-card p-6 shadow-sm">
            <div class="grid gap-1 text-sm">
                <p>
                    <span class="text-muted-foreground">Référence de transaction : </span>
                    <span class="font-medium">{{ order.trans_ref ?? '—' }}</span>
                </p>
                <p class="flex items-center gap-2">
                    <span class="text-muted-foreground">Statut du paiement :</span>
                    <Badge v-if="order.trans_state" variant="outline" size="sm">{{ order.trans_state }}</Badge>
                    <span v-else>—</span>
                </p>
            </div>
            <form class="flex items-center gap-2" @submit.prevent="submit">
                <NativeSelect v-model="form.etat" aria-label="État de la vente">
                    <option v-for="item in state" :key="item" :value="item">{{ item }}</option>
                </NativeSelect>
                <Button type="submit" :disabled="form.processing || !form.isDirty">Valider</Button>
            </form>
            <p v-if="form.errors.etat" class="w-full text-sm text-destructive" role="alert">{{ form.errors.etat }}</p>
        </section>

        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <section v-for="block in infos" :key="block.title" class="rounded-lg border bg-card p-5 shadow-sm">
                <h2 class="mb-3 text-sm font-semibold">{{ block.title }}</h2>
                <dl class="grid gap-2 text-sm">
                    <div v-for="row in block.rows" :key="row.label">
                        <dt class="text-xs text-muted-foreground">{{ row.label }}</dt>
                        <dd class="font-medium break-words">{{ row.value }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
            <div>
                <h2 class="mb-3 text-lg font-semibold">Produits ({{ products.length }})</h2>
                <DataTable :rows="products" :columns="5">
                    <template #head>
                        <TableHead>Référence</TableHead>
                        <TableHead>Produit</TableHead>
                        <TableHead class="text-right">Prix unitaire</TableHead>
                        <TableHead class="text-right">Quantité</TableHead>
                        <TableHead class="text-right">Total</TableHead>
                    </template>

                    <template #row="{ row }">
                        <TableCell class="whitespace-nowrap text-muted-foreground">{{ row.reference }}</TableCell>
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="row.cover"
                                    :src="row.cover"
                                    :alt="`Cover de ${row.nom}`"
                                    class="size-10 rounded-md border object-cover"
                                />
                                <div v-else class="flex size-10 items-center justify-center rounded-md border bg-muted/40">
                                    <ImageOff class="size-4 text-muted-foreground" aria-hidden="true" />
                                </div>
                                <span class="font-medium">{{ row.nom }}</span>
                            </div>
                        </TableCell>
                        <TableCell class="text-right whitespace-nowrap tabular-nums">{{ formatXof(row.prix) }}</TableCell>
                        <TableCell class="text-right tabular-nums">{{ row.pivot?.quantity }}</TableCell>
                        <TableCell class="text-right whitespace-nowrap tabular-nums">
                            {{ formatXof(row.pivot?.montant ?? 0) }}
                        </TableCell>
                    </template>

                    <template #empty>Cette vente ne contient aucun produit.</template>
                </DataTable>
                <p class="mt-3 text-right text-sm">
                    <span class="text-muted-foreground">Montant total : </span>
                    <span class="text-lg font-semibold tabular-nums">{{ formatXof(Number(order.totaux ?? 0)) }}</span>
                </p>
            </div>

            <section class="h-fit rounded-lg border bg-card p-5 shadow-sm">
                <h2 class="mb-2 text-sm font-semibold">Commentaire</h2>
                <p class="text-sm whitespace-pre-line text-muted-foreground">
                    {{ order.commentaire || 'Aucun commentaire.' }}
                </p>
            </section>
        </div>
    </AdminLayoutV2>
</template>

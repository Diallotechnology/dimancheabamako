<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Eye, ImageOff, Pencil, Plus } from '@lucide/vue';
import { product as productIndex } from '@/actions/App/Http/Controllers/AdminController';
import { destroy as destroyImage, edit as editImage } from '@/actions/App/Http/Controllers/ImageController';
import { show as showOrder } from '@/actions/App/Http/Controllers/OrderController';
import { edit } from '@/actions/App/Http/Controllers/ProductController';
import ConfirmDelete from '@/Components/Admin/ConfirmDelete.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { TableCell, TableHead } from '@/Components/ui/table';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Product } from '@/types';

const props = defineProps<{
    product: Product;
}>();

const price = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 });

const images = props.product.images ?? [];
const orders = props.product.orders ?? [];

const details: { label: string; value: string }[] = [
    { label: 'Catégorie', value: props.product.categorie?.nom ?? '—' },
    { label: 'Prix', value: price.format(props.product.prix) },
    { label: 'Stock', value: String(props.product.stock) },
    { label: 'Poids', value: `${props.product.poids} Kg` },
    { label: 'Couleur', value: props.product.color ?? '—' },
    { label: 'Taille', value: props.product.taille ? `${props.product.taille} M` : '—' },
];
</script>

<template>
    <AdminLayoutV2 :title="product.nom" :description="`Référence ${product.reference}`">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="productIndex()">
                    <ArrowLeft />
                    Retour aux produits
                </Link>
            </Button>
            <Button as-child>
                <Link :href="edit(product.id)">
                    <Pencil />
                    Modifier
                </Link>
            </Button>
        </template>

        <section class="mb-8 grid gap-6 rounded-lg border bg-card p-6 shadow-sm lg:grid-cols-[14rem_1fr]">
            <div class="flex aspect-square w-full max-w-56 items-center justify-center overflow-hidden rounded-md border bg-muted/40">
                <img v-if="product.cover" :src="product.cover" :alt="`Cover de ${product.nom}`" class="size-full object-cover" />
                <ImageOff v-else class="size-8 text-muted-foreground" aria-hidden="true" />
            </div>
            <div class="grid gap-4">
                <div class="flex flex-wrap gap-2">
                    <Badge v-if="product.status === 'draft'" variant="outline">Brouillon</Badge>
                    <Badge v-if="product.is_preorder" variant="secondary">Sur commande</Badge>
                    <Badge v-if="product.favoris">Favori</Badge>
                </div>
                <dl class="grid gap-4 text-sm sm:grid-cols-3">
                    <div v-for="item in details" :key="item.label">
                        <dt class="text-xs text-muted-foreground">{{ item.label }}</dt>
                        <dd class="font-medium">{{ item.value }}</dd>
                    </div>
                </dl>
                <div v-if="product.resume" class="text-sm">
                    <p class="text-xs text-muted-foreground">Description courte</p>
                    <p class="whitespace-pre-line">{{ product.resume }}</p>
                </div>
                <div v-if="product.description" class="text-sm">
                    <p class="text-xs text-muted-foreground">Description</p>
                    <p class="whitespace-pre-line">{{ product.description }}</p>
                </div>
                <video v-if="product.video" :src="product.video" controls class="max-h-64 w-full max-w-md rounded-md border" />
            </div>
        </section>

        <section class="mb-8">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 class="text-lg font-semibold">Images ({{ images.length }})</h2>
                <Button as-child variant="outline" size="sm">
                    <Link :href="edit(product.id)">
                        <Plus />
                        Ajouter des images
                    </Link>
                </Button>
            </div>
            <p v-if="images.length === 0" class="text-sm text-muted-foreground">Aucune image pour ce produit.</p>
            <div v-else class="grid gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                <figure v-for="image in images" :key="image.id" class="overflow-hidden rounded-lg border bg-card shadow-sm">
                    <img :src="image.chemin" :alt="`Image de ${product.nom}`" class="aspect-square w-full object-cover" />
                    <figcaption class="flex justify-end gap-1 p-2">
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            aria-label="Remplacer cette image"
                        >
                            <Link :href="editImage(image.id)">
                                <Pencil />
                            </Link>
                        </Button>
                        <ConfirmDelete
                            :action="destroyImage(image.id)"
                            what="cette image"
                            success-message="Image supprimée."
                        />
                    </figcaption>
                </figure>
            </div>
        </section>

        <h2 class="mb-3 text-lg font-semibold">Commandes ({{ orders.length }})</h2>
        <DataTable :rows="orders" :columns="10">
            <template #head>
                <TableHead class="w-16">#</TableHead>
                <TableHead>Référence</TableHead>
                <TableHead>Adresse</TableHead>
                <TableHead>Postal</TableHead>
                <TableHead>Ville</TableHead>
                <TableHead>Pays</TableHead>
                <TableHead>Paiement</TableHead>
                <TableHead>État</TableHead>
                <TableHead>Date</TableHead>
                <TableHead class="w-16 text-right">Voir</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell class="text-muted-foreground">{{ row.id }}</TableCell>
                <TableCell class="font-medium">{{ row.reference }}</TableCell>
                <TableCell>{{ row.adresse }}</TableCell>
                <TableCell>{{ row.postal }}</TableCell>
                <TableCell>{{ row.ville }}</TableCell>
                <TableCell>{{ row.country?.nom ?? '—' }}</TableCell>
                <TableCell>
                    <p>{{ row.trans_ref }}</p>
                    <p class="text-xs text-muted-foreground">{{ row.trans_state }}</p>
                </TableCell>
                <TableCell><Badge variant="outline">{{ row.etat }}</Badge></TableCell>
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

            <template #empty>Aucune commande pour ce produit.</template>
        </DataTable>
    </AdminLayoutV2>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Eye, Heart, ImageOff, Pencil, Plus, Search, Upload, X } from '@lucide/vue';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { product as productIndex } from '@/actions/App/Http/Controllers/AdminController';
import { create, destroy, edit, favoris_update, show } from '@/actions/App/Http/Controllers/ProductController';
import { create as importCreate } from '@/actions/App/Http/Controllers/ProductImportController';
import ConfirmDelete from '@/Components/Admin/ConfirmDelete.vue';
import DataTable from '@/Components/Admin/DataTable.vue';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { NativeSelect } from '@/Components/ui/native-select';
import { TableCell, TableHead } from '@/Components/ui/table';
import { useFilter } from '@/Composables/useFilter';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Category, Paginated, Product } from '@/types';

const props = defineProps<{
    rows: Paginated<Product>;
    category: Pick<Category, 'id' | 'nom'>[];
}>();

// Une valeur vide = « pas de filtre » : « Tous les produits » ne doit jamais envoyer favoris=0.
const { filters, resetFilters } = useFilter(
    {
        search: '',
        category: '',
        favoris: '',
        status: '',
        statut: '',
    },
    productIndex,
);

const hasFilter = (): boolean => Object.values(filters.value).some((value) => value !== '');

const price = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 });

// Produits dont le favori est en cours de bascule : évite le double clic pendant l'appel.
const pending = ref<Set<number>>(new Set());

/** Bascule le favori par un appel axios (réponse 204) puis recharge uniquement les lignes. */
const toggleFavori = async (row: Product): Promise<void> => {
    if (pending.value.has(row.id)) {
        return;
    }

    const next = row.favoris ? 0 : 1;
    pending.value.add(row.id);

    try {
        await window.axios.put(favoris_update({ data: next, product_id: row.id }).url);
        toast.success(next === 1 ? `« ${row.nom} » ajouté aux favoris.` : `« ${row.nom} » retiré des favoris.`);
        router.reload({ only: ['rows'] });
    } catch {
        toast.error('Impossible de modifier le favori. Réessayez ou rechargez la page.');
    } finally {
        pending.value.delete(row.id);
    }
};

// Supprimer la dernière ligne d'une page > 1 ramène à la page précédente.
const afterDelete = (): void => {
    if (props.rows.data.length === 1 && props.rows.current_page > 1) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', String(props.rows.current_page - 1));
        router.get(url.pathname + url.search, {}, { preserveState: true, replace: true });
    }
};
</script>

<template>
    <AdminLayoutV2 title="Produits" description="Le catalogue de la boutique : stock, prix, favoris et publication.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="importCreate()">
                    <Upload />
                    Import par photos
                </Link>
            </Button>
            <Button as-child>
                <Link :href="create()">
                    <Plus />
                    Nouveau produit
                </Link>
            </Button>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative w-full max-w-xs">
                <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    v-model="filters.search"
                    type="search"
                    placeholder="Rechercher un produit…"
                    aria-label="Rechercher un produit"
                    class="pl-9"
                />
            </div>
            <NativeSelect v-model="filters.category" aria-label="Filtrer par catégorie">
                <option value="">Toutes les catégories</option>
                <option v-for="item in category" :key="item.id" :value="String(item.id)">{{ item.nom }}</option>
            </NativeSelect>
            <NativeSelect v-model="filters.favoris" aria-label="Filtrer par favori">
                <option value="">Tous les produits</option>
                <option value="1">Favoris</option>
                <option value="0">Hors favoris</option>
            </NativeSelect>
            <NativeSelect v-model="filters.status" aria-label="Filtrer par disponibilité">
                <option value="">Toute disponibilité</option>
                <option value="1">Sur commande</option>
                <option value="0">En stock</option>
            </NativeSelect>
            <NativeSelect v-model="filters.statut" aria-label="Filtrer par publication">
                <option value="">Toute publication</option>
                <option value="draft">Brouillons</option>
                <option value="published">Publiés</option>
            </NativeSelect>
            <Button v-if="hasFilter()" variant="ghost" @click="resetFilters">
                <X />
                Réinitialiser
            </Button>
        </div>

        <DataTable :rows="rows" :columns="8" item-label="produits">
            <template #head>
                <TableHead class="w-16">Photo</TableHead>
                <TableHead>Produit</TableHead>
                <TableHead>Catégorie</TableHead>
                <TableHead>Prix</TableHead>
                <TableHead>Stock</TableHead>
                <TableHead>Poids</TableHead>
                <TableHead>État</TableHead>
                <TableHead class="w-44 text-right">Actions</TableHead>
            </template>

            <template #row="{ row }">
                <TableCell>
                    <img
                        v-if="row.cover"
                        :src="row.cover"
                        :alt="`Cover de ${row.nom}`"
                        class="size-12 rounded-md border object-cover"
                    />
                    <div v-else class="flex size-12 items-center justify-center rounded-md border bg-muted/40">
                        <ImageOff class="size-5 text-muted-foreground" aria-hidden="true" />
                    </div>
                </TableCell>
                <TableCell>
                    <p class="font-medium">{{ row.nom }}</p>
                    <p class="text-xs text-muted-foreground">
                        Réf. {{ row.reference }}<template v-if="row.color"> · {{ row.color }}</template>
                        <template v-if="row.taille"> · {{ row.taille }} M</template>
                    </p>
                </TableCell>
                <TableCell>{{ row.categorie?.nom ?? '—' }}</TableCell>
                <TableCell class="whitespace-nowrap">{{ price.format(row.prix) }}</TableCell>
                <TableCell>{{ row.stock }}</TableCell>
                <TableCell class="whitespace-nowrap">{{ row.poids }} Kg</TableCell>
                <TableCell>
                    <div class="flex flex-wrap gap-1">
                        <Badge v-if="row.status === 'draft'" variant="outline">Brouillon</Badge>
                        <Badge v-if="row.is_preorder" variant="secondary">Sur commande</Badge>
                    </div>
                </TableCell>
                <TableCell>
                    <div class="flex justify-end gap-1">
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            :class="row.favoris ? 'text-rose-600' : 'text-muted-foreground'"
                            :disabled="pending.has(row.id)"
                            :aria-pressed="row.favoris"
                            :aria-label="row.favoris ? `Retirer ${row.nom} des favoris` : `Ajouter ${row.nom} aux favoris`"
                            @click="toggleFavori(row)"
                        >
                            <Heart :class="{ 'fill-current': row.favoris }" />
                        </Button>
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Voir le produit ${row.nom}`"
                        >
                            <Link :href="show(row.id)">
                                <Eye />
                            </Link>
                        </Button>
                        <Button
                            as-child
                            variant="ghost"
                            size="icon-sm"
                            class="text-muted-foreground"
                            :aria-label="`Modifier le produit ${row.nom}`"
                        >
                            <Link :href="edit(row.id)">
                                <Pencil />
                            </Link>
                        </Button>
                        <ConfirmDelete
                            :action="destroy(row.id)"
                            :what="`le produit ${row.nom}`"
                            consequence="Le produit, sa cover, sa vidéo et ses images seront supprimés."
                            success-message="Produit supprimé."
                            @deleted="afterDelete"
                        />
                    </div>
                </TableCell>
            </template>

            <template #empty>
                <template v-if="hasFilter()">Aucun produit ne correspond à ces filtres.</template>
                <div v-else class="flex flex-col items-center gap-3">
                    Aucun produit pour le moment.
                    <Button as-child size="sm">
                        <Link :href="create()">
                            <Plus />
                            Créer un produit
                        </Link>
                    </Button>
                </div>
            </template>
        </DataTable>
    </AdminLayoutV2>
</template>

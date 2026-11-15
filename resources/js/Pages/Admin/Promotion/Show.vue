<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, Eye, Pencil } from '@lucide/vue';
import { promotion as promotionIndex } from '@/actions/App/Http/Controllers/AdminController';
import { show as productShow } from '@/actions/App/Http/Controllers/ProductController';
import { edit } from '@/actions/App/Http/Controllers/PromotionController';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Promotion } from '@/types';

defineProps<{
    /** `debut` et `fin` sont les valeurs brutes de la base (PromotionController::show). */
    promotion: Promotion;
}>();

const price = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' });
</script>

<template>
    <AdminLayoutV2 :title="promotion.nom" description="Détail de la promotion et produits concernés.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="promotionIndex()">
                    <ArrowLeft />
                    Retour aux promotions
                </Link>
            </Button>
            <Button as-child>
                <Link :href="edit(promotion.id)">
                    <Pencil />
                    Modifier
                </Link>
            </Button>
        </template>

        <Card class="mb-6">
            <CardContent>
                <dl class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm text-muted-foreground">Réduction</dt>
                        <dd class="text-lg font-medium">{{ promotion.reduction }} %</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-muted-foreground">Début</dt>
                        <dd class="font-medium">{{ promotion.debut }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-muted-foreground">Fin</dt>
                        <dd class="font-medium">{{ promotion.fin }}</dd>
                    </div>
                </dl>
            </CardContent>
        </Card>

        <h2 class="mb-3 text-base font-semibold">Produits concernés ({{ promotion.products?.length ?? 0 }})</h2>
        <p v-if="!promotion.products?.length" class="text-sm text-muted-foreground">
            Aucun produit rattaché à cette promotion.
        </p>
        <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <Card v-for="product in promotion.products" :key="product.id">
                <CardHeader>
                    <CardTitle class="text-base">{{ product.nom }}</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <p class="text-lg font-semibold">{{ price.format(product.prix) }}</p>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
                        <dt class="text-muted-foreground">Catégorie</dt>
                        <dd>{{ product.categorie?.nom ?? '—' }}</dd>
                        <dt class="text-muted-foreground">Couleur</dt>
                        <dd>{{ product.color ?? '—' }}</dd>
                        <dt class="text-muted-foreground">Taille</dt>
                        <dd>{{ product.taille ?? '—' }}</dd>
                        <dt class="text-muted-foreground">Poids</dt>
                        <dd>{{ product.poids }}</dd>
                        <dt class="text-muted-foreground">Stock</dt>
                        <dd>{{ product.stock }}</dd>
                    </dl>
                    <div>
                        <Badge :variant="product.favoris ? 'default' : 'secondary'">
                            {{ product.favoris ? 'Favori' : 'Non favori' }}
                        </Badge>
                    </div>
                    <Button as-child variant="outline" size="sm" class="justify-self-start">
                        <Link :href="productShow(product.id)">
                            <Eye />
                            Voir le produit
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AdminLayoutV2>
</template>

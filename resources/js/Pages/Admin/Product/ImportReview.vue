<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, TriangleAlert } from '@lucide/vue';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { product as productIndex } from '@/actions/App/Http/Controllers/AdminController';
import { update } from '@/actions/App/Http/Controllers/ProductController';
import Combobox from '@/Components/Admin/Combobox.vue';
import FormField from '@/Components/Admin/FormField.vue';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Textarea } from '@/Components/ui/textarea';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Option, Product } from '@/types';

/** Colonnes lues par `ProductImportController::review` (cast décimal : `poids` peut arriver en chaîne). */
type ReviewProduct = Pick<
    Product,
    | 'id'
    | 'categorie_id'
    | 'reference'
    | 'nom'
    | 'resume'
    | 'description'
    | 'color'
    | 'taille'
    | 'prix'
    | 'stock'
    | 'favoris'
    | 'is_preorder'
    | 'cover'
    | 'status'
> & { poids: number | string };

/** Champs envoyés à `ProductController@update` (mêmes règles que l'édition normale). */
interface ReviewFields {
    categorie_id: string;
    reference: string;
    nom: string;
    color: string;
    taille: string;
    description: string;
    resume: string;
    poids: string;
    prix: string;
    stock: string;
    /** Stock affiché à l'ouverture : le serveur n'applique que l'écart avec `stock`. */
    stock_initial: string;
    favoris: 0 | 1;
    is_preorder: 0 | 1;
    status: 'published';
    _method: 'PUT';
}

type ReviewForm = InertiaForm<ReviewFields>;

const props = defineProps<{
    batchId: string;
    products: ReviewProduct[];
    /** Catégories : valeur = id (chaîne). */
    category: Option[];
}>();

const published = computed(() => props.products.filter((product) => product.status === 'published').length);

/**
 * Prix et poids à 0 sont des valeurs par défaut de l'IA, pas de vraies valeurs : on les affiche
 * vides pour forcer la saisie.
 */
const positiveOrEmpty = (value: number | string): string => (Number(value) > 0 ? String(Number(value)) : '');

const fieldsFrom = (product: ReviewProduct): ReviewFields => ({
    categorie_id: product.categorie_id ? String(product.categorie_id) : '',
    reference: product.reference,
    nom: product.nom,
    color: product.color ?? '',
    taille: product.taille ?? '',
    description: product.description ?? '',
    resume: product.resume,
    poids: positiveOrEmpty(product.poids),
    prix: positiveOrEmpty(product.prix),
    stock: String(product.stock),
    stock_initial: String(product.stock),
    favoris: product.favoris ? 1 : 0,
    is_preorder: product.is_preorder ? 1 : 0,
    status: 'published',
    // Multipart : PHP ne lit pas un PUT, l'envoi se fait en POST avec `_method`.
    _method: 'PUT',
});

// Un formulaire par produit, indexé par id. Construits une fois : les erreurs et l'état d'envoi
// restent propres à chaque carte.
const forms: Record<number, ReviewForm> = Object.fromEntries(
    props.products.map((product) => [product.id, useForm<ReviewFields>(fieldsFrom(product))]),
);

const save = (product: ReviewProduct): void => {
    const form = forms[product.id];

    form.post(update(product.id).url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Le stock saisi est maintenant le stock réel : un second enregistrement ne doit pas
            // réappliquer l'écart depuis l'ouverture de la page.
            form.stock_initial = form.stock;
            toast.success(`« ${form.nom} » enregistré.`);
        },
        onError: () => {
            toast.error('Le formulaire contient des erreurs.');
        },
    });
};
</script>

<template>
    <AdminLayoutV2 title="Revue de l'import" description="Complétez chaque fiche générée par l'IA puis publiez-la.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="productIndex({ query: { statut: 'draft' } })">
                    <ArrowLeft />
                    Tous les brouillons
                </Link>
            </Button>
        </template>

        <div class="grid max-w-5xl gap-5">
            <Alert variant="warning">
                <TriangleAlert />
                <AlertDescription>
                    <p>
                        Complétez le prix, le poids, la couleur et la taille de chaque produit puis enregistrez : il sera
                        publié dans la boutique. {{ published }} / {{ products.length }} publié(s).
                    </p>
                </AlertDescription>
            </Alert>

            <p v-if="products.length === 0" class="text-sm text-muted-foreground">Aucun produit pour cet import.</p>

            <article
                v-for="product in products"
                :key="product.id"
                class="grid gap-5 rounded-lg border bg-card p-5 shadow-sm md:grid-cols-[12rem_1fr]"
            >
                <div class="grid content-start justify-items-center gap-2">
                    <img :src="product.cover" :alt="product.nom" class="aspect-square w-full rounded-md border object-cover" />
                    <Badge v-if="product.status === 'draft'" variant="outline">Brouillon</Badge>
                    <Badge v-else variant="success">Publié</Badge>
                </div>

                <form class="grid gap-5" novalidate @submit.prevent="save(product)">
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <FormField
                            label="Nom"
                            :error="forms[product.id].errors.nom"
                            required
                            class="sm:col-span-2"
                            v-slot="field"
                        >
                            <Input
                                :id="field.id"
                                v-model="forms[product.id].nom"
                                placeholder="Le nom du produit"
                                :aria-invalid="field.invalid"
                                :aria-describedby="field.describedBy"
                            />
                        </FormField>
                        <FormField
                            label="Catégorie"
                            :error="forms[product.id].errors.categorie_id"
                            class="sm:col-span-2"
                            v-slot="field"
                        >
                            <Combobox
                                :id="field.id"
                                v-model="forms[product.id].categorie_id"
                                :options="category"
                                placeholder="Choisir une catégorie"
                                search-placeholder="Rechercher une catégorie…"
                                :invalid="field.invalid"
                                :described-by="field.describedBy"
                            />
                        </FormField>
                        <FormField label="Prix (en CFA)" :error="forms[product.id].errors.prix" required v-slot="field">
                            <Input
                                :id="field.id"
                                v-model="forms[product.id].prix"
                                type="number"
                                min="0"
                                step="1"
                                placeholder="Le prix"
                                :aria-invalid="field.invalid"
                                :aria-describedby="field.describedBy"
                            />
                        </FormField>
                        <FormField label="Poids (en Kg)" :error="forms[product.id].errors.poids" required v-slot="field">
                            <Input
                                :id="field.id"
                                v-model="forms[product.id].poids"
                                placeholder="Le poids"
                                :aria-invalid="field.invalid"
                                :aria-describedby="field.describedBy"
                            />
                        </FormField>
                        <FormField label="Stock" :error="forms[product.id].errors.stock" required v-slot="field">
                            <Input
                                :id="field.id"
                                v-model="forms[product.id].stock"
                                type="number"
                                min="0"
                                step="1"
                                :aria-invalid="field.invalid"
                                :aria-describedby="field.describedBy"
                            />
                        </FormField>
                        <FormField label="Couleur" :error="forms[product.id].errors.color" required v-slot="field">
                            <Input
                                :id="field.id"
                                v-model="forms[product.id].color"
                                placeholder="La couleur"
                                :aria-invalid="field.invalid"
                                :aria-describedby="field.describedBy"
                            />
                        </FormField>
                        <FormField label="Taille (en M)" :error="forms[product.id].errors.taille" required v-slot="field">
                            <Input
                                :id="field.id"
                                v-model="forms[product.id].taille"
                                placeholder="La taille"
                                :aria-invalid="field.invalid"
                                :aria-describedby="field.describedBy"
                            />
                        </FormField>
                        <FormField
                            label="Description courte"
                            :error="forms[product.id].errors.resume"
                            required
                            class="sm:col-span-2 lg:col-span-4"
                            v-slot="field"
                        >
                            <Textarea
                                :id="field.id"
                                v-model="forms[product.id].resume"
                                placeholder="La description courte du produit"
                                :aria-invalid="field.invalid"
                                :aria-describedby="field.describedBy"
                            />
                        </FormField>
                        <FormField
                            label="Description"
                            :error="forms[product.id].errors.description"
                            class="sm:col-span-2 lg:col-span-4"
                            v-slot="field"
                        >
                            <Textarea
                                :id="field.id"
                                v-model="forms[product.id].description"
                                placeholder="La description du produit"
                                :aria-invalid="field.invalid"
                                :aria-describedby="field.describedBy"
                            />
                        </FormField>
                    </div>

                    <div class="flex justify-end">
                        <Button type="submit" :disabled="forms[product.id].processing">
                            <Loader2 v-if="forms[product.id].processing" class="animate-spin" />
                            {{ product.status === 'draft' ? 'Enregistrer et publier' : 'Enregistrer' }}
                        </Button>
                    </div>
                </form>
            </article>
        </div>
    </AdminLayoutV2>
</template>

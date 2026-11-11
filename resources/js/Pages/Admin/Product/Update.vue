<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { product as productIndex } from '@/actions/App/Http/Controllers/AdminController';
import { show, update } from '@/actions/App/Http/Controllers/ProductController';
import ProductFormFields from '@/Components/Admin/ProductFormFields.vue';
import { Button } from '@/Components/ui/button';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Option, Product, ProductFields } from '@/types';

const props = defineProps<{
    /** Catégories : valeur = id (chaîne). */
    category: Option[];
    product: Product;
}>();

type UpdateFields = ProductFields & {
    /** Stock affiché à l'ouverture : le serveur n'applique que l'écart avec `stock`. */
    stock_initial: string;
    _method: 'PUT';
};

/** Valeurs du formulaire à partir du produit serveur ; les fichiers restent vides (jamais un chemin ni un tableau serveur). */
const fieldsFrom = (product: Product): UpdateFields => ({
    nom: product.nom,
    reference: product.reference,
    categorie_id: product.categorie_id === null ? '' : String(product.categorie_id),
    color: product.color ?? '',
    taille: product.taille ?? '',
    poids: String(product.poids),
    prix: String(product.prix),
    stock: String(product.stock),
    stock_initial: String(product.stock),
    resume: product.resume,
    description: product.description ?? '',
    favoris: product.favoris,
    is_preorder: product.is_preorder,
    cover: null,
    image: [],
    video: null,
    // PHP ne lit pas un multipart envoyé en PUT : envoi en POST, `_method` indique PUT à Laravel.
    _method: 'PUT',
});

const form = useForm<UpdateFields>(fieldsFrom(props.product));
const page = usePage<{ product: Product }>();

const submit = (): void => {
    // Le serveur valide `boolean` : il n'accepte que true/false/1/0, pas les chaînes d'un multipart.
    form
        .transform((data) => ({
            ...data,
            favoris: data.favoris ? 1 : 0,
            is_preorder: data.is_preorder ? 1 : 0,
        }))
        .post(update(props.product.id).url, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                // La redirection a déjà rechargé le produit : on repart de lui (stock réel, médias
                // enregistrés), sans jamais réinjecter une valeur serveur dans un champ fichier.
                form.defaults(fieldsFrom(page.props.product));
                form.reset();
                toast.success('Produit mis à jour.');
            },
            onError: () => {
                toast.error('Le formulaire contient des erreurs.');
            },
        });
};
</script>

<template>
    <AdminLayoutV2 :title="`Modifier « ${product.nom} »`" description="Sans nouveau fichier, la cover, la vidéo et les images actuelles sont conservées.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="show(product.id)">
                    <ArrowLeft />
                    Retour au produit
                </Link>
            </Button>
        </template>

        <form class="grid max-w-3xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <ProductFormFields
                :form="form"
                :category="category"
                :current-cover="product.cover"
                :current-video="product.video"
                :current-image-count="product.images?.length ?? 0"
            />
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="productIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing || !form.isDirty">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Enregistrer
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

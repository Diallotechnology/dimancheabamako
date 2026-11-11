<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { product as productIndex } from '@/actions/App/Http/Controllers/AdminController';
import { store } from '@/actions/App/Http/Controllers/ProductController';
import ProductFormFields from '@/Components/Admin/ProductFormFields.vue';
import { Button } from '@/Components/ui/button';
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';
import type { Option, ProductFields } from '@/types';

defineProps<{
    /** Catégories : valeur = id (chaîne). */
    category: Option[];
}>();

const form = useForm<ProductFields>({
    nom: '',
    reference: '',
    categorie_id: '',
    color: '',
    taille: '',
    poids: '',
    prix: '',
    stock: '1',
    resume: '',
    description: '',
    favoris: false,
    is_preorder: false,
    cover: null,
    image: [],
    video: null,
});

const submit = (): void => {
    // Le serveur valide `boolean` : il n'accepte que true/false/1/0, pas les chaînes d'un multipart.
    form
        .transform((data) => ({
            ...data,
            favoris: data.favoris ? 1 : 0,
            is_preorder: data.is_preorder ? 1 : 0,
        }))
        .submit(store(), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                toast.success('Produit ajouté.');
            },
            onError: () => {
                toast.error('Le formulaire contient des erreurs.');
            },
        });
};
</script>

<template>
    <AdminLayoutV2 title="Nouveau produit" description="Les informations du produit, sa cover, ses images et une vidéo facultative.">
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="productIndex()">
                    <ArrowLeft />
                    Retour aux produits
                </Link>
            </Button>
        </template>

        <form class="grid max-w-3xl gap-5 rounded-lg border bg-card p-6 shadow-sm" novalidate @submit.prevent="submit">
            <ProductFormFields :form="form" :category="category" creating />
            <div class="flex justify-end gap-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="productIndex()">Annuler</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Loader2 v-if="form.processing" class="animate-spin" />
                    Créer le produit
                </Button>
            </div>
        </form>
    </AdminLayoutV2>
</template>

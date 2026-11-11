<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import Combobox from '@/Components/Admin/Combobox.vue';
import FormField from '@/Components/Admin/FormField.vue';
import ImageUpload from '@/Components/Admin/ImageUpload.vue';
import MultiImageUpload from '@/Components/Admin/MultiImageUpload.vue';
import VideoUpload from '@/Components/Admin/VideoUpload.vue';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import type { Option, ProductFields } from '@/types';

/**
 * Champs du formulaire produit, partagés par Create et Update. La page garde la soumission
 * (route, `_method`, conversion des booléens) ; ce composant ne fait que lier `form` aux contrôles.
 */
const props = defineProps<{
    form: InertiaForm<ProductFields>;
    /** Catégories au format `Options::from()` : valeur = id (chaîne). */
    category: Option[];
    /** Médias déjà enregistrés (édition) : affichés tant qu'aucun nouveau fichier n'est choisi. */
    currentCover?: string | null;
    currentVideo?: string | null;
    /** Nombre d'images déjà enregistrées (édition) ; les nouvelles s'ajoutent à celles-ci. */
    currentImageCount?: number;
    /** À la création, cover et images sont obligatoires. */
    creating?: boolean;
}>();

// Les erreurs de tableau arrivent par fichier (`image.0`, `image.1`…) : on remonte la première.
const imageError = computed(() => {
    const key = Object.keys(props.form.errors).find((name) => name === 'image' || name.startsWith('image.'));

    return key ? (props.form.errors as Record<string, string>)[key] : undefined;
});
</script>

<template>
    <div class="grid gap-5 sm:grid-cols-2">
        <FormField label="Nom" :error="form.errors.nom" required v-slot="field">
            <Input
                :id="field.id"
                v-model="form.nom"
                placeholder="Le nom du produit"
                :aria-invalid="field.invalid"
                :aria-describedby="field.describedBy"
            />
        </FormField>
        <FormField label="Référence" :error="form.errors.reference" required v-slot="field">
            <Input
                :id="field.id"
                v-model="form.reference"
                placeholder="La référence du produit"
                :aria-invalid="field.invalid"
                :aria-describedby="field.describedBy"
            />
        </FormField>
        <FormField label="Catégorie" :error="form.errors.categorie_id" v-slot="field">
            <Combobox
                :id="field.id"
                v-model="form.categorie_id"
                :options="category"
                placeholder="Choisir une catégorie"
                search-placeholder="Rechercher une catégorie…"
                :invalid="field.invalid"
                :described-by="field.describedBy"
            />
        </FormField>
        <FormField label="Couleur" :error="form.errors.color" :required="!creating" v-slot="field">
            <Input
                :id="field.id"
                v-model="form.color"
                placeholder="La couleur du produit"
                :aria-invalid="field.invalid"
                :aria-describedby="field.describedBy"
            />
        </FormField>
        <FormField label="Taille (en M)" :error="form.errors.taille" :required="!creating" v-slot="field">
            <Input
                :id="field.id"
                v-model="form.taille"
                placeholder="2"
                :aria-invalid="field.invalid"
                :aria-describedby="field.describedBy"
            />
        </FormField>
        <FormField label="Poids (en Kg)" :error="form.errors.poids" required v-slot="field">
            <Input
                :id="field.id"
                v-model="form.poids"
                placeholder="1.5"
                :aria-invalid="field.invalid"
                :aria-describedby="field.describedBy"
            />
        </FormField>
        <FormField label="Prix (en CFA)" :error="form.errors.prix" required v-slot="field">
            <Input
                :id="field.id"
                v-model="form.prix"
                type="number"
                min="0"
                step="1"
                placeholder="Le prix du produit"
                :aria-invalid="field.invalid"
                :aria-describedby="field.describedBy"
            />
        </FormField>
        <FormField
            label="Stock"
            :error="form.errors.stock"
            :hint="creating ? undefined : 'Saisissez le stock voulu : seul l\'écart avec le stock affiché est appliqué.'"
            required
            v-slot="field"
        >
            <Input
                :id="field.id"
                v-model="form.stock"
                type="number"
                min="0"
                step="1"
                :aria-invalid="field.invalid"
                :aria-describedby="field.describedBy"
            />
        </FormField>

        <div class="grid gap-3 sm:col-span-2 sm:grid-cols-2">
            <div class="flex items-center gap-2">
                <input
                    id="product-favoris"
                    v-model="form.favoris"
                    type="checkbox"
                    class="size-4 rounded border-input accent-primary"
                />
                <Label for="product-favoris">Produit favori</Label>
            </div>
            <div class="flex items-center gap-2">
                <input
                    id="product-preorder"
                    v-model="form.is_preorder"
                    type="checkbox"
                    class="size-4 rounded border-input accent-primary"
                />
                <Label for="product-preorder">Uniquement sur commande</Label>
            </div>
        </div>

        <FormField
            label="Cover"
            hint="JPG, PNG ou WebP, 600×600, 5 Mo maximum."
            :error="form.errors.cover"
            :required="creating"
            class="sm:col-span-2"
            v-slot="field"
        >
            <ImageUpload
                :id="field.id"
                v-model="form.cover"
                :current-url="currentCover"
                :invalid="field.invalid"
                :described-by="field.describedBy"
                :progress="form.progress?.percentage"
            />
        </FormField>
        <FormField
            label="Images du produit"
            :hint="
                creating
                    ? 'JPG, PNG ou WebP, 600×600, 5 Mo maximum chacune.'
                    : `${currentImageCount ?? 0} image(s) enregistrée(s) : les nouvelles s'y ajoutent (gestion fine sur la fiche produit).`
            "
            :error="imageError"
            :required="creating"
            class="sm:col-span-2"
            v-slot="field"
        >
            <MultiImageUpload
                :id="field.id"
                v-model="form.image"
                :invalid="field.invalid"
                :described-by="field.describedBy"
            />
        </FormField>
        <FormField
            label="Vidéo (facultative)"
            hint="MP4, MOV ou WebM, 50 Mo maximum."
            :error="form.errors.video"
            class="sm:col-span-2"
            v-slot="field"
        >
            <VideoUpload
                :id="field.id"
                v-model="form.video"
                :current-url="currentVideo"
                :invalid="field.invalid"
                :described-by="field.describedBy"
            />
        </FormField>

        <FormField label="Description courte" :error="form.errors.resume" required class="sm:col-span-2" v-slot="field">
            <Textarea
                :id="field.id"
                v-model="form.resume"
                placeholder="La description courte du produit"
                :aria-invalid="field.invalid"
                :aria-describedby="field.describedBy"
            />
        </FormField>
        <FormField label="Description" :error="form.errors.description" class="sm:col-span-2" v-slot="field">
            <Textarea
                :id="field.id"
                v-model="form.description"
                placeholder="La description du produit"
                :aria-invalid="field.invalid"
                :aria-describedby="field.describedBy"
            />
        </FormField>
    </div>
</template>

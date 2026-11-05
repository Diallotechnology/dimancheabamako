<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import notify from "@/helper";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    batchId: { type: String, required: true },
    products: { type: Array, required: true },
    category: { type: Array, required: true },
});

const published = computed(
    () => props.products.filter((p) => p.status === "published").length
);

// Un formulaire par carte, soumis à ProductController@update (mêmes règles que l'édition normale).
// Prix et poids à 0 sont affichés vides pour forcer la saisie.
const forms = Object.fromEntries(
    props.products.map((p) => [
        p.id,
        useForm({
            categorie_id: p.categorie_id ? `${p.categorie_id}` : "",
            reference: p.reference,
            nom: p.nom,
            color: p.color ?? "",
            taille: p.taille ?? "",
            description: p.description ?? "",
            resume: p.resume ?? "",
            poids: Number(p.poids) > 0 ? `${Number(p.poids)}` : "",
            prix: p.prix > 0 ? p.prix : "",
            stock: p.stock,
            stock_initial: p.stock,
            favoris: p.favoris ? 1 : 0,
            is_preorder: p.is_preorder ? 1 : 0,
            status: "published",
            _method: "PUT",
        }),
    ])
);

const save = (product) => {
    forms[product.id].post(route("product.update", product.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => notify("Produit enregistré avec succès !", true),
        onError: () => notify(false),
    });
};
</script>

<template>
    <Head title="Revue de l'import" />

    <AuthenticatedLayout>
        <div class="content-header">
            <h2 class="content-title card-title">Revue de l'import</h2>
            <Link
                :href="route('product', { statut: 'draft' })"
                class="btn btn-light btn-sm rounded"
            >
                Tous les brouillons
            </Link>
        </div>

        <div class="alert alert-warning">
            Complétez le prix, le poids, la couleur et la taille de chaque
            produit puis enregistrez : il sera publié dans la boutique.
            {{ published }} / {{ products.length }} publié(s).
        </div>

        <p v-if="products.length === 0" class="text-muted">
            Aucun produit pour cet import.
        </p>

        <div v-for="product in products" :key="product.id" class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <img
                            :src="product.cover"
                            :alt="product.nom"
                            class="img-fluid rounded mb-2"
                        />
                        <div>
                            <span
                                v-if="product.status === 'draft'"
                                class="badge rounded-pill alert-warning"
                                >Brouillon</span
                            >
                            <span
                                v-else
                                class="badge rounded-pill alert-success"
                                >Publié</span
                            >
                        </div>
                    </div>
                    <div class="col-md-9">
                        <form @submit.prevent="save(product)">
                            <div class="row">
                                <div class="col-md-6">
                                    <Input
                                        input_type="text"
                                        place="le nom du produit"
                                        label="nom"
                                        v-model="forms[product.id].nom"
                                        :message="forms[product.id].errors.nom"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <Select2
                                        label="categorie"
                                        :message="
                                            forms[product.id].errors
                                                .categorie_id
                                        "
                                    >
                                        <VueSelect
                                            placeholder="selectionner"
                                            v-model="
                                                forms[product.id].categorie_id
                                            "
                                            :options="category"
                                        />
                                    </Select2>
                                </div>
                                <div class="col-md-3">
                                    <Input
                                        input_type="number"
                                        label="prix en CFA"
                                        place="le prix"
                                        v-model="forms[product.id].prix"
                                        :message="forms[product.id].errors.prix"
                                        required
                                    />
                                </div>
                                <div class="col-md-3">
                                    <Input
                                        input_type="text"
                                        label="poids en Kg"
                                        place="le poids"
                                        v-model="forms[product.id].poids"
                                        :message="
                                            forms[product.id].errors.poids
                                        "
                                        required
                                    />
                                </div>
                                <div class="col-md-3">
                                    <Input
                                        input_type="number"
                                        label="stock"
                                        place="le stock"
                                        v-model="forms[product.id].stock"
                                        :message="
                                            forms[product.id].errors.stock
                                        "
                                        required
                                    />
                                </div>
                                <div class="col-md-3">
                                    <Input
                                        input_type="text"
                                        label="Couleur"
                                        place="la couleur"
                                        v-model="forms[product.id].color"
                                        :message="
                                            forms[product.id].errors.color
                                        "
                                        required
                                    />
                                </div>
                                <div class="col-md-3">
                                    <Input
                                        input_type="text"
                                        label="taille"
                                        place="la taille"
                                        v-model="forms[product.id].taille"
                                        :message="
                                            forms[product.id].errors.taille
                                        "
                                        required
                                    />
                                </div>
                                <TextArea
                                    place="la description courte du produit"
                                    label="description courte"
                                    v-model="forms[product.id].resume"
                                    :message="forms[product.id].errors.resume"
                                    required
                                />
                                <TextArea
                                    place="la description du produit"
                                    label="description"
                                    v-model="forms[product.id].description"
                                    :message="
                                        forms[product.id].errors.description
                                    "
                                />
                            </div>
                            <div class="text-end">
                                <button
                                    type="submit"
                                    class="btn btn-primary rounded"
                                    :disabled="forms[product.id].processing"
                                >
                                    {{
                                        product.status === "draft"
                                            ? "Enregistrer et publier"
                                            : "Enregistrer"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

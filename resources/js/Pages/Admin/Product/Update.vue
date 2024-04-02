<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";

const props = defineProps({
    category: {
        required: true,
        type: Object,
        default: () => ({}),
    },
    product: {
        required: true,
        type: Object,
        default: () => ({}),
    },
});
const form = useForm({
    categorie_id: `${props.product.categorie_id}`,
    reference: props.product.reference,
    nom: props.product.nom,
    color: props.product.color,
    taille: props.product.taille,
    description: props.product.description,
    resume: props.product.resume,
    poids: props.product.poids,
    prix: props.product.prix,
    stock: props.product.stock,
    favoris: props.product.favoris,
    cover: null,
    image: [],
    video: null,
    _method: "PUT",
});

const submit = () => {
    form.post(route("product.update", props.product.id), {
        forceFormData: true,
        onSuccess: () => {
            form.categorie_id = `${props.product.categorie_id}`;
            form.reference = props.product.reference;
            form.nom = props.product.nom;
            form.color = props.product.color;
            form.taille = props.product.taille;
            form.description = props.product.description;
            form.resume = props.product.resume;
            form.poids = props.product.poids;
            form.prix = props.product.prix;
            form.stock = props.product.stock;
            form.favoris = props.product.favoris;
            form.cover = props.product.cover;
            form.image = props.product.image;
            form.video = props.product.video;
            notify("produit mise à jour avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>
<template>
    <AuthenticatedLayout>
        <div class="card">
            <h2 class="p-4 text-center">Formulaire de mise à jour</h2>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6">
                            <Input
                                input_type="text"
                                place="le nom du produit"
                                label="nom"
                                v-model="form.nom"
                                :message="form.errors.nom"
                                required
                            />
                        </div>
                        <div class="col-md-6">
                            <Input
                                input_type="text"
                                place="la reference du produit"
                                label="reference"
                                v-model="form.reference"
                                :message="form.errors.reference"
                                required
                            />
                        </div>
                        <div class="col-md-6">
                            <Select2
                                label="categorie"
                                :message="form.errors.categorie_id"
                            >
                                <VueSelect
                                    placeholder="selectionner"
                                    v-model="form.categorie_id"
                                    :options="category"
                                />
                            </Select2>
                        </div>
                        <div class="col-md-6">
                            <label for="">Favoris</label>
                            <select v-model="form.favoris" class="form-select">
                                <option value="1">OUI</option>
                                <option value="0">NON</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <Input
                                input_type="text"
                                place="la couleur du produit"
                                label="Couleur"
                                v-model="form.color"
                                :message="form.errors.color"
                            />
                        </div>
                        <div class="col-md-6">
                            <Input
                                input_type="text"
                                place="la taille du produit"
                                label="taille"
                                v-model="form.taille"
                                :message="form.errors.taille"
                            />
                        </div>
                        <div class="col-md-6">
                            <Input
                                input_type="text"
                                label="poids en Kg (pas de decimal)"
                                place="le poids du produit"
                                v-model="form.poids"
                                :message="form.errors.poids"
                                required
                            />
                        </div>
                        <div class="col-md-6">
                            <Input
                                input_type="number"
                                label="prix en CFA"
                                place="le prix du produit"
                                v-model="form.prix"
                                :message="form.errors.prix"
                                required
                            />
                        </div>
                        <div class="col-md-6">
                            <Input
                                input_type="number"
                                label="stock"
                                place="le stock du produit"
                                v-model="form.stock"
                                :message="form.errors.stock"
                                required
                            />
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="text-uppercase form-label"
                                    >Cover (600X600)</label
                                >
                                <input
                                    class="form-control"
                                    name="cover"
                                    @input="form.cover = $event.target.files[0]"
                                    type="file"
                                />
                                <div v-show="form.errors.cover">
                                    <p class="text-sm text-danger">
                                        {{ form.errors.cover }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="text-uppercase form-label"
                                    >Images du produit</label
                                >
                                <input
                                    class="form-control"
                                    name="image"
                                    multiple
                                    @input="form.image = $event.target.files"
                                    type="file"
                                />
                                <div v-show="form.errors.image">
                                    <p class="text-sm text-danger">
                                        {{ form.errors.image }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="text-uppercase form-label"
                                    >Video Faculatif</label
                                >
                                <input
                                    class="form-control"
                                    name="video"
                                    @input="form.video = $event.target.files[0]"
                                    type="file"
                                />
                                <div v-show="form.errors.video">
                                    <p class="text-sm text-danger">
                                        {{ form.errors.video }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <TextArea
                            place="la description courte du produit"
                            label="description courte"
                            v-model="form.resume"
                            :message="form.errors.resume"
                            required
                        />
                        <TextArea
                            place="la description du produit"
                            label="description"
                            v-model="form.description"
                            :message="form.errors.description"
                            required
                        />
                    </div>
                    <progress
                        v-if="form.progress"
                        :value="form.progress.percentage"
                        max="100"
                    >
                        {{ form.progress.percentage }}%
                    </progress>
                    <div class="modal-footer">
                        <Link
                            :href="route('product')"
                            class="btn btn-danger rounded"
                            data-bs-dismiss="modal"
                        >
                            Annuler
                        </Link>
                        <button
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            type="submit"
                            class="btn btn-primary rounded"
                        >
                            Valider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

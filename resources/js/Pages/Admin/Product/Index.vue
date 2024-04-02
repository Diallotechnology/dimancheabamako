<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import ButtonShow from "@/Components/ButtonShow.vue";
import Modal from "@/Components/Modal.vue";
import TextArea from "@/Components/TextArea.vue";
import notify, { Price_format } from "@/helper";
import Pagination from "@/Components/Pagination.vue";
import { ref, watch } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
const props = defineProps({
    rows: {
        required: true,
        type: Object,
        default: () => ({}),
    },
    category: {
        required: true,
        type: Object,
        default: () => ({}),
    },
    filter: {
        required: true,
        type: Object,
        default: () => ({}),
    },
});

const filters = ref({
    search: props.filter.search,
    cat: props.filter.cat,
});

watch(filters, (value) => {
    setTimeout(() => {
        router.get(
            "/admin/product",
            { filters: value },
            { preserveState: true, replace: true }
        );
    }, 600);
});
const SelectFilter = () => {
    router.get(
        "/admin/product",
        { cat: filters.cat.value },
        { preserveState: true, replace: true }
    );
};
const form = useForm({
    categorie_id: "",
    reference: "",
    nom: "",
    color: "",
    taille: "",
    description: "",
    resume: "",
    poids: "",
    prix: "",
    favoris: 0,
    stock: 1,
    cover: "",
    image: [],
    video: null,
});

const submit = () => {
    form.post(route("product.store"), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            notify("Produit ajouter avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
const favori = (url) => {
    axios
        .get(url)
        .then((response) => {
            notify("Produit ajouter comme favori avec success !", true);
            router.reload();
        })
        .catch(function (error) {
            console.log(error);
        });
};
</script>

<template>
    <Head title="Produit" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Liste des produits</h2>
            </div>
            <div>
                <a
                    href="#"
                    class="btn btn-primary btn-sm rounded"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#exampleModal"
                >
                    <i class="material-icons md-plus md-18"></i>

                    Nouveau</a
                >
            </div>
        </div>
        <div class="card mb-4">
            <header class="card-header">
                <div class="row gx-3">
                    <div class="col-lg-4 col-md-6 me-auto">
                        <input
                            type="text"
                            v-model="filters.search"
                            placeholder="Recherche..."
                            class="form-control"
                        />
                    </div>
                    <div class="col-6 col-md-3">
                        <Select2
                            v-model="filters.cat"
                            :data="category"
                            label=""
                        />
                    </div>
                </div>
            </header>
            <!-- card-header end// -->
            <div class="card-body">
                <div
                    class="row gx-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4"
                >
                    <div class="col" v-for="row in rows.data" :key="row.id">
                        <div class="card card-product-grid">
                            <a href="#" class="img-wrap">
                                <img
                                    v-bind:src="row.cover"
                                    alt="Produit cover"
                                />
                            </a>
                            <div class="info-wrap">
                                <p class="title">{{ row.nom }}</p>
                                <p class="title">
                                    Categorie {{ row.categorie.nom }}
                                </p>
                                <p class="title">Couleur {{ row.color }}</p>
                                <p class="title">Taille {{ row.taille }}</p>
                                <p class="title">Poids {{ row.poids }}</p>
                                <p class="title">Stock {{ row.stock }}</p>
                                <p class="title">favori {{ row.favoris }}</p>
                                <div class="price mb-2">
                                    {{ Price_format.format(row.prix) }}
                                </div>
                                <!-- price.// -->
                                <ButtonShow
                                    :href="route('product.show', row.id)"
                                />
                                <ButtonEdit
                                    :href="route('product.edit', row.id)"
                                />
                                <ButtonDelete
                                    :url="route('product.destroy', row.id)"
                                />
                                <button
                                    type="button"
                                    @click.prevent="
                                        favori(
                                            route('product.favoris', [
                                                row.id,
                                                !row.favoris ? 1 : 0,
                                            ])
                                        )
                                    "
                                    class="btn btn-sm btn-success font-sm rounded mx-1"
                                >
                                    <i
                                        :class="[
                                            !row.favoris
                                                ? 'material-icons md-favorite_border text-light'
                                                : 'material-icons md-favorite text-light',
                                        ]"
                                    ></i>
                                    <!-- Edit -->
                                </button>
                            </div>
                        </div>
                        <!-- card-product  end// -->
                    </div>
                </div>
                <!-- row.// -->
            </div>
            <!-- card-body end// -->
        </div>
        <!-- card end// -->
        <Pagination :pagination="rows" />
        <Modal name="Formulaire de nouveau produit">
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
                            label="taille en M"
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
                        <label for="">Favoris</label>
                        <select v-model="form.favoris" class="form-select">
                            <option value="1">OUI</option>
                            <option value="0">NON</option>
                        </select>
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
                                required
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
                                >Images du produit 600x600</label
                            >
                            <input
                                class="form-control"
                                name="image"
                                multiple
                                @input="form.image = $event.target.files"
                                type="file"
                                required
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
                    <button
                        type="button"
                        class="btn btn-danger rounded"
                        data-bs-dismiss="modal"
                    >
                        Fermer
                    </button>
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
        </Modal>
    </AuthenticatedLayout>
</template>

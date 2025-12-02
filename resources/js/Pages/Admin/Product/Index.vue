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
import { Head, Link, router, useForm } from "@inertiajs/vue3";

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
});

const filters = ref({
    search: "",
    category: "",
    favoris: "",
    status: "",
    color: "",
    taille: "",
    price_min: "",
    price_max: "",
    sort: "",
});

// debounce propre
let timeout = null;
watch(
    filters,
    (val) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            router.get("/admin/product", val, {
                preserveState: true,
                replace: true,
            });
        }, 450);
    },
    { deep: true }
);

const resetFilters = () => {
    filters.value = {
        search: "",
        category: "",
        favoris: "",
        status: "",
    };
};

const favori = (url) => {
    axios
        .get(url)
        .then(() => {
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
                <Link
                    :href="route('product.create')"
                    class="btn btn-primary btn-sm rounded"
                    type="button"
                >
                    <i class="material-icons md-plus md-18"></i>

                    Nouveau</Link
                >
            </div>
        </div>
        <div class="card mb-4">
            <header class="card-header">
                <div class="row gx-3">
                    <div class="col-md-4 mb-lg-0 mb-15 me-auto">
                        <input
                            type="text"
                            placeholder="Search..."
                            v-model="filters.search"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" v-model="filters.category">
                            <option value="">Toutes les catégories</option>
                            <option
                                v-for="item in category"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.nom }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" v-model="filters.favoris">
                            <option value="">Produits favoris</option>
                            <option value="1">OUI</option>
                            <option value="0">NO</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" v-model="filters.status">
                            <option value="">Produits Status</option>
                            <option value="1">Sur commande</option>
                            <option value="0">Non</option>
                        </select>
                    </div>
                    <div class="col-md-2"></div>
                    <div class="col-auto">
                        <button
                            @click="resetFilters"
                            class="btn btn-danger rounded"
                        >
                            Reset<i
                                class="material-icons md-delete_forever md-18"
                            ></i>
                        </button>
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
                                    Reférence {{ row.reference }}
                                </p>
                                <p class="title">
                                    Categorie {{ row.categorie.nom }}
                                </p>
                                <p class="title">Couleur {{ row.color }}</p>
                                <p class="title">Taille {{ row.taille }} M</p>
                                <p class="title">Poids {{ row.poids }} Kg</p>
                                <p class="title">Stock {{ row.stock }}</p>
                                <p class="title">
                                    Favoris {{ row.favoris ? "Oui" : "Non" }}
                                </p>
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
                                            route('product.favori', [
                                                !row.favoris ? 1 : 0,
                                                row.id,
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
                <h4 class="text-center my-3" v-if="rows.data.length === 0">
                    Aucun element
                </h4>
                <!-- row.// -->
            </div>
            <!-- card-body end// -->
        </div>
        <!-- card end// -->
        <Pagination :pagination="rows" />
        <Modal name="Formulaire de nouveau produit"> </Modal>
    </AuthenticatedLayout>
</template>

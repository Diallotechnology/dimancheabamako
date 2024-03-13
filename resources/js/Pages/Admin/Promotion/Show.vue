<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ButtonShow from "@/Components/ButtonShow.vue";
import { Price_format } from "@/helper";
const props = defineProps({
    promotion: {
        type: Object,
        default: () => ({}),
    },
});
</script>
<template>
    <AuthenticatedLayout>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-4">
                    <!--  col.// -->
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <p>Nom: {{ promotion.nom }}</p>
                        <p>reduction: {{ promotion.reduction }} %</p>
                        <p>debut: {{ promotion.debut }}</p>
                        <p>fin: {{ promotion.fin }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div
                    class="row gx-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4"
                >
                    <div
                        class="col"
                        v-for="row in promotion.products"
                        :key="row.id"
                    >
                        <div class="card card-product-grid">
                            <a href="#" class="img-wrap">
                                <img
                                    v-bind:src="'/admin/assets/imgs/items/1.jpg'"
                                    alt="Product"
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
                                <p class="title">
                                    favori {{ row.favoris ? "OUI" : "NON" }}
                                </p>
                                <div class="price mb-2">
                                    {{ Price_format.format(row.prix) }}
                                </div>
                                <!-- price.// -->
                                <ButtonShow
                                    :href="route('product.show', row.id)"
                                />
                            </div>
                        </div>
                        <!-- card-product  end// -->
                    </div>
                </div>
                <!-- row.// -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>

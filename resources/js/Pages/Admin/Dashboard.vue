<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
const props = defineProps({
    order: {
        type: Number,
        default: () => 0,
    },
    product: {
        type: Number,
        default: () => 0,
    },
    revenu: {
        type: Number,
        default: () => 0,
    },
    categorie: {
        type: Number,
        default: () => 0,
    },
});
let Price_format = new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "XOF",
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Tableau de bord</h2>
                <p>Whole data about your business here</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <div class="card card-body mb-4">
                    <article class="icontext">
                        <span
                            class="icon icon-sm rounded-circle bg-primary-light"
                            ><i
                                class="text-primary material-icons md-monetization_on"
                            ></i
                        ></span>
                        <div class="text">
                            <h6 class="mb-1 card-title">Total Revenue</h6>
                            <span>{{ Price_format.format(revenu) }}</span>
                            <span class="text-sm">
                                Shipping fees are not included
                            </span>
                        </div>
                    </article>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-body mb-4">
                    <article class="icontext">
                        <span
                            class="icon icon-sm rounded-circle bg-success-light"
                            ><i
                                class="text-success material-icons md-local_shipping"
                            ></i
                        ></span>
                        <div class="text">
                            <h6 class="mb-1 card-title">Total Ventes</h6>
                            <span>{{ order }}</span>
                            <span class="text-sm">
                                Excluding orders in transit
                            </span>
                        </div>
                    </article>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-body mb-4">
                    <article class="icontext">
                        <span
                            class="icon icon-sm rounded-circle bg-warning-light"
                            ><i
                                class="text-warning material-icons md-qr_code"
                            ></i
                        ></span>
                        <div class="text">
                            <h6 class="mb-1 card-title">Total Produits</h6>
                            <span>{{ product }}</span>
                            <span class="text-sm"> In 19 Categories </span>
                        </div>
                    </article>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-body mb-4">
                    <article class="icontext">
                        <span class="icon icon-sm rounded-circle bg-info-light"
                            ><i
                                class="text-info material-icons md-shopping_basket"
                            ></i
                        ></span>
                        <div class="text">
                            <h6 class="mb-1 card-title">Total Categories</h6>
                            <span>{{ categorie }}</span>
                            <span class="text-sm">
                                Based in your local time.
                            </span>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

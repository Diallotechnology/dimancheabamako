<script setup>
import Layout from "@/Shared/Layout.vue";
import { AddToCard } from "@/helper";
import { onMounted, ref } from "vue";
import Cart from "@/Shared/Cart.vue";
import { Head } from "@inertiajs/vue3";
import { Thumbs, Navigation, FreeMode } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";

import "swiper/css";
import "swiper/css/free-mode";
import "swiper/css/navigation";
import "swiper/css/thumbs";

const props = defineProps({
    product: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    rows: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    category: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const thumbsSwiper = ref(null);
const setThumbsSwiper = (swiper) => {
    thumbsSwiper.value = swiper;
};
</script>
<template>
    <Head title="{{ product.resume }}" />
    <Layout>
        <section class="mt-50 mb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">
                        <div class="product-detail accordion-detail mb-4">
                            <div class="row mb-50">
                                <div class="col-md-6 col-sm-12 col-xs-12">
                                    <swiper
                                        :modules="[Thumbs]"
                                        :thumbs="{ swiper: thumbsSwiper }"
                                    >
                                        <swiper-slide
                                            v-for="row in product.images"
                                            :key="row"
                                            ><img
                                                v-bind:src="row.chemin"
                                                alt="product image"
                                        /></swiper-slide>
                                    </swiper>

                                    <swiper
                                        :modules="[Thumbs]"
                                        watch-slides-progress
                                        @swiper="setThumbsSwiper"
                                        :spaceBetween="10"
                                        :slidesPerView="4"
                                        :freeMode="true"
                                        :watchSlidesProgress="true"
                                    >
                                        <swiper-slide
                                            v-for="row in product.images"
                                            :key="row"
                                            ><img
                                                v-bind:src="row.chemin"
                                                alt="product image"
                                        /></swiper-slide>
                                    </swiper>

                                    <!-- End Gallery -->
                                </div>
                                <div class="col-md-6 col-sm-12 col-xs-12">
                                    <div class="detail-info">
                                        <h2 class="title-detail">
                                            {{ product.nom }}
                                        </h2>
                                        <div class="product-detail-rating">
                                            <div class="pro-details-brand">
                                                <span>
                                                    Categorie:
                                                    <a href="">
                                                        {{
                                                            product.categorie
                                                                .nom
                                                        }}</a
                                                    ></span
                                                >
                                            </div>
                                        </div>
                                        <div
                                            class="clearfix product-price-cover"
                                        >
                                            <div
                                                class="product-price primary-color float-left"
                                            >
                                                <ins
                                                    ><span class="text-brand">
                                                        {{
                                                            product.reduction >
                                                            0
                                                                ? product.prix_promo
                                                                : product.prix_format
                                                        }}
                                                    </span>
                                                </ins>
                                                <ins>
                                                    <span
                                                        v-if="
                                                            product.reduction >
                                                            0
                                                        "
                                                        class="old-price font-md ml-15"
                                                        >{{
                                                            product.prix_format
                                                        }}</span
                                                    >
                                                </ins>
                                                <span
                                                    class="save-price font-md color3 ml-15 text-danger"
                                                    >{{
                                                        product.reduction > 0
                                                            ? "Bon plan -" +
                                                              product.reduction +
                                                              "%"
                                                            : ""
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                        <div
                                            class="bt-1 border-color-1 mt-15 mb-15"
                                        ></div>
                                        <div class="short-desc mb-30">
                                            <p>
                                                {{ product.resume }}
                                            </p>
                                        </div>
                                        <div
                                            class="product_sort_info font-xs mb-30"
                                        >
                                            <ul>
                                                <li class="mb-10">
                                                    <i
                                                        class="fi-rs-crown mr-5"
                                                    ></i
                                                    >Taille:
                                                    {{ product.taille }}
                                                </li>

                                                <li>
                                                    En stock:
                                                    {{
                                                        product.stock > 1
                                                            ? "OUI"
                                                            : "NON"
                                                    }}
                                                </li>
                                            </ul>
                                        </div>

                                        <div
                                            class="bt-1 border-color-1 mt-30 mb-30"
                                        ></div>
                                        <div class="detail-extralink">
                                            <div class="product-extra-link2">
                                                <button
                                                    @click.prevent="
                                                        AddToCard(
                                                            route(
                                                                'cart.store',
                                                                product.id
                                                            )
                                                        )
                                                    "
                                                    type="submit"
                                                    class="button button-add-to-cart"
                                                >
                                                    Acheté
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Detail Info -->
                                </div>
                            </div>
                            <div class="tab-style3">
                                <ul class="nav nav-tabs text-uppercase">
                                    <li class="nav-item">
                                        <a
                                            class="nav-link active"
                                            id="Description-tab"
                                            data-bs-toggle="tab"
                                            href="#Description"
                                            >Description du produit</a
                                        >
                                    </li>
                                </ul>
                                <div
                                    class="tab-content shop_info_tab entry-main-content"
                                >
                                    <div
                                        class="tab-pane fade show active"
                                        id="Description"
                                    >
                                        <div class="">
                                            <p>
                                                {{ product.description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="row related-products mt-4">
                            <div class="row product-grid-4">
                                <Cart :items="rows" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </Layout>
</template>

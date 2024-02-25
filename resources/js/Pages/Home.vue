<script setup>
import { onMounted } from "vue";
import Layout from "@/Shared/Layout.vue";
import Cart from "@/Shared/Cart.vue";
import { Link } from "@inertiajs/vue3";
const props = defineProps({
    popular: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    latest: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    slide: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});

onMounted(() => {
    $(".hero-slider-1").slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        fade: true,
        loop: true,
        dots: true,
        arrows: true,
        prevArrow:
            '<span class="slider-btn slider-prev"><i class="fi-rs-angle-left"></i></span>',
        nextArrow:
            '<span class="slider-btn slider-next"><i class="fi-rs-angle-right"></i></span>',
        appendArrows: ".hero-slider-1-arrow",
        autoplay: true,
    });
});
</script>
<template>
    <Layout>
        <section class="home-slider position-relative pt-50">
            <div class="hero-slider-1 dot-style-1 dot-style-1-position-1">
                <div
                    class="single-hero-slider single-animation-wrap"
                    v-for="item in slide"
                    :key="item.id"
                >
                    <div class="container">
                        <div class="row align-items-center slider-animated-1">
                            <div class="col-lg-5 col-md-6">
                                <div class="hero-slider-content-2">
                                    <h2 class="animated fw-900">
                                        {{ item.text_one }}
                                    </h2>
                                    <h1 class="animated fw-900 text-brand">
                                        {{ item.text_two }}
                                    </h1>
                                    <p class="animated">
                                        {{ item.paragraph }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-6">
                                <div
                                    class="single-slider-img single-slider-img-1"
                                >
                                    <img
                                        class="animated slider-1-1"
                                        v-bind:src="item.image"
                                        alt="slide image"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slider-arrow hero-slider-1-arrow"></div>
        </section>
        <section class="featured section-padding position-relative">
            <div class="container">
                <div class="row">
                    <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                        <div
                            class="banner-features wow fadeIn animated hover-up"
                        >
                            <img
                                src="assets/imgs/theme/icons/feature-1.png"
                                alt=""
                            />
                            <h4 class="bg-1">Livraison partout</h4>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                        <div
                            class="banner-features wow fadeIn animated hover-up"
                        >
                            <img
                                src="assets/imgs/theme/icons/feature-2.png"
                                alt=""
                            />
                            <h4 class="bg-3">Commande en ligne</h4>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                        <div
                            class="banner-features wow fadeIn animated hover-up"
                        >
                            <img
                                src="assets/imgs/theme/icons/feature-3.png"
                                alt=""
                            />
                            <h4 class="bg-2">Économiser de l'argent</h4>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                        <div
                            class="banner-features wow fadeIn animated hover-up"
                        >
                            <img
                                src="assets/imgs/theme/icons/feature-4.png"
                                alt=""
                            />
                            <h4 class="bg-4">Promotions</h4>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                        <div
                            class="banner-features wow fadeIn animated hover-up"
                        >
                            <img
                                src="assets/imgs/theme/icons/feature-5.png"
                                alt=""
                            />
                            <h4 class="bg-5">Bon shopping</h4>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                        <div
                            class="banner-features wow fadeIn animated hover-up"
                        >
                            <img
                                src="assets/imgs/theme/icons/feature-6.png"
                                alt=""
                            />
                            <h4 class="bg-6">24/7 Support</h4>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section
            class="product-tabs section-padding position-relative wow fadeIn animated"
        >
            <div class="bg-square"></div>
            <div class="container">
                <div class="tab-header">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active"
                                id="nav-tab-two"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-two"
                                type="button"
                                role="tab"
                                aria-controls="tab-two"
                                aria-selected="false"
                            >
                                Populaire
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="nav-tab-three"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-three"
                                type="button"
                                role="tab"
                                aria-controls="tab-three"
                                aria-selected="false"
                            >
                                Nouveauté
                            </button>
                        </li>
                    </ul>
                    <Link
                        :href="route('shop')"
                        class="view-more d-none d-md-flex"
                        >Voir tout<i class="fi-rs-angle-double-small-right"></i
                    ></Link>
                </div>
                <!--End nav-tabs-->
                <div class="tab-content wow fadeIn animated" id="myTabContent">
                    <div
                        class="tab-pane fade show active"
                        id="tab-two"
                        role="tabpanel"
                        aria-labelledby="tab-two"
                    >
                        <div class="row product-grid-4">
                            <Cart :items="popular" :hot="true" />
                        </div>
                        <!--End product-grid-4-->
                    </div>
                    <!--En tab two (Popular)-->
                    <div
                        class="tab-pane fade"
                        id="tab-three"
                        role="tabpanel"
                        aria-labelledby="tab-three"
                    >
                        <div class="row product-grid-4">
                            <Cart :items="latest" :news="true" />
                        </div>
                        <!--End product-grid-4-->
                    </div>
                    <!--En tab three (New added)-->
                </div>
                <!--End tab-content-->
            </div>
        </section>
    </Layout>
</template>
@/helper

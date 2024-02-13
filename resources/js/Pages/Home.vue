<script setup>
import { onMounted } from "vue";
import Layout from "@/Shared/Layout.vue";
import notify, { Price_format } from "@/notifications";
import { Link } from "@inertiajs/vue3";
const props = defineProps({
    product: {
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
                <div class="single-hero-slider single-animation-wrap">
                    <div class="container">
                        <div class="row align-items-center slider-animated-1">
                            <div class="col-lg-5 col-md-6">
                                <div class="hero-slider-content-2">
                                    <h4 class="animated">Trade-in offer</h4>
                                    <h2 class="animated fw-900">
                                        Supper value deals
                                    </h2>
                                    <h1 class="animated fw-900 text-brand">
                                        On all products
                                    </h1>
                                    <p class="animated">
                                        Save more with coupons & up to 70% off
                                    </p>
                                    <a
                                        class="animated btn btn-brush btn-brush-3"
                                        href=""
                                    >
                                        Shop Now
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-6">
                                <div
                                    class="single-slider-img single-slider-img-1"
                                >
                                    <img
                                        class="animated slider-1-1"
                                        v-bind:src="'/assets/imgs/slider/slider-1.png'"
                                        alt=""
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="single-hero-slider single-animation-wrap">
                    <div class="container">
                        <div class="row align-items-center slider-animated-1">
                            <div class="col-lg-5 col-md-6">
                                <div class="hero-slider-content-2">
                                    <h4 class="animated">Hot promotions</h4>
                                    <h2 class="animated fw-900">
                                        Fashion Trending
                                    </h2>
                                    <h1 class="animated fw-900 text-7">
                                        Great Collection
                                    </h1>
                                    <p class="animated">
                                        Save more with coupons & up to 20% off
                                    </p>
                                    <a
                                        class="animated btn btn-brush btn-brush-2"
                                        href=""
                                    >
                                        Discover Now
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-6">
                                <div
                                    class="single-slider-img single-slider-img-1"
                                >
                                    <img
                                        class="animated slider-1-2"
                                        v-bind:srcsrc="'/assets/imgs/slider/slider-2.png'"
                                        alt=""
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="single-hero-slider single-animation-wrap">
                    <div class="container">
                        <div class="row align-items-center slider-animated-1">
                            <div class="col-lg-5 col-md-6">
                                <div class="hero-slider-content-2">
                                    <h4 class="animated">Upcoming Offer</h4>
                                    <h2 class="animated fw-900">
                                        Big Deals From
                                    </h2>
                                    <h1 class="animated fw-900 text-8">
                                        Manufacturer
                                    </h1>
                                    <p class="animated">
                                        Clothing, Shoes, Bags, Wallets...
                                    </p>
                                    <a
                                        class="animated btn btn-brush btn-brush-1"
                                        href=""
                                    >
                                        Shop Now
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-6">
                                <div
                                    class="single-slider-img single-slider-img-1"
                                >
                                    <img
                                        class="animated slider-1-3"
                                        v-bind:src="'/assets/imgs/slider/slider-3.png'"
                                        alt=""
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
                    <a :href="route('shop')" class="view-more d-none d-md-flex"
                        >Voir tout<i class="fi-rs-angle-double-small-right"></i
                    ></a>
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
                            <div
                                class="col-lg-3 col-md-4 col-12 col-sm-6"
                                v-for="item in product"
                                :key="item"
                            >
                                <div class="product-cart-wrap mb-30">
                                    <div class="product-img-action-wrap">
                                        <div
                                            class="product-img product-img-zoom"
                                        >
                                            <Link
                                                :href="
                                                    route('shop.show', item.id)
                                                "
                                            >
                                                <img
                                                    class="default-img"
                                                    src="assets/imgs/shop/product-2-2.jpg"
                                                    alt=""
                                                />
                                                <img
                                                    class="hover-img"
                                                    src="assets/imgs/shop/product-3-2.jpg"
                                                    alt=""
                                                />
                                            </Link>
                                        </div>
                                        <div class="product-action-1">
                                            <a
                                                aria-label="Voir"
                                                class="action-btn hover-up"
                                                data-bs-toggle="modal"
                                                data-bs-target="#quickViewModal"
                                                ><i class="fi-rs-eye"></i
                                            ></a>
                                            <a
                                                aria-label="Add To Wishlist"
                                                class="action-btn hover-up"
                                                href="shop-wishlist.html"
                                                ><i class="fi-rs-heart"></i
                                            ></a>
                                        </div>
                                        <div
                                            class="product-badges product-badges-position product-badges-mrg"
                                        >
                                            <span class="hot">Hot</span>
                                        </div>
                                    </div>
                                    <div class="product-content-wrap">
                                        <div class="product-category">
                                            <Link
                                                :href="
                                                    route('shop.show', item.id)
                                                "
                                            >
                                                Categorie:
                                                {{ item.categorie.nom }}
                                            </Link>
                                        </div>
                                        <h2>
                                            <Link
                                                :href="
                                                    route('shop.show', item.id)
                                                "
                                            >
                                                {{ item.nom }}
                                            </Link>
                                        </h2>
                                        <span>Couleur {{ item.color }}</span>
                                        <br />
                                        <span>Taille {{ item.taille }}</span>
                                        <div class="product-price">
                                            <span>
                                                {{
                                                    Price_format.format(
                                                        item.prix
                                                    )
                                                }}
                                            </span>
                                            <span class="old-price"
                                                >$245.8</span
                                            >
                                        </div>
                                        <div class="product-action-1 show">
                                            <a
                                                aria-label="Acheté"
                                                class="action-btn hover-up"
                                                href="shop-cart.html"
                                                ><i
                                                    class="fi-rs-shopping-bag-add"
                                                ></i
                                            ></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            <div class="col-lg-3 col-md-4 col-12 col-sm-6">
                                <div class="product-cart-wrap mb-30">
                                    <div class="product-img-action-wrap">
                                        <div
                                            class="product-img product-img-zoom"
                                        >
                                            <a href="shop-product-right.html">
                                                <img
                                                    class="default-img"
                                                    src="assets/imgs/shop/product-2-1.jpg"
                                                    alt=""
                                                />
                                                <img
                                                    class="hover-img"
                                                    src="assets/imgs/shop/product-2-2.jpg"
                                                    alt=""
                                                />
                                            </a>
                                        </div>
                                        <div class="product-action-1">
                                            <a
                                                aria-label="Quick view"
                                                class="action-btn hover-up"
                                                data-bs-toggle="modal"
                                                data-bs-target="#quickViewModal"
                                                ><i class="fi-rs-eye"></i
                                            ></a>
                                            <a
                                                aria-label="Add To Wishlist"
                                                class="action-btn hover-up"
                                                href="shop-wishlist.html"
                                                ><i class="fi-rs-heart"></i
                                            ></a>
                                            <a
                                                aria-label="Compare"
                                                class="action-btn hover-up"
                                                href="shop-compare.html"
                                                ><i class="fi-rs-shuffle"></i
                                            ></a>
                                        </div>
                                        <div
                                            class="product-badges product-badges-position product-badges-mrg"
                                        >
                                            <span class="hot">Hot</span>
                                        </div>
                                    </div>
                                    <div class="product-content-wrap">
                                        <div class="product-category">
                                            <a href="shop-grid-right.html"
                                                >Music</a
                                            >
                                        </div>
                                        <h2>
                                            <a href="shop-product-right.html"
                                                >Donec ut nisl rutrum</a
                                            >
                                        </h2>
                                        <div class="rating-result" title="90%">
                                            <span>
                                                <span>90%</span>
                                            </span>
                                        </div>
                                        <div class="product-price">
                                            <span>$238.85 </span>
                                            <span class="old-price"
                                                >$245.8</span
                                            >
                                        </div>
                                        <div class="product-action-1 show">
                                            <a
                                                aria-label="Add To Cart"
                                                class="action-btn hover-up"
                                                href="shop-cart.html"
                                                ><i
                                                    class="fi-rs-shopping-bag-add"
                                                ></i
                                            ></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

<script setup>
import Layout from "@/Shared/Layout.vue";
import { AddToCard } from "@/helper";
import { onMounted, ref } from "vue";
import Cart from "@/Shared/Cart.vue";
import { usePage } from "@inertiajs/vue3";

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
const page = usePage();
const local = page.props.locale;
const taux = ref();
const getDevise = async () => {
    try {
        await axios.get(route("devise.taux")).then((response) => {
            taux.value = response.data;
        });
    } catch (error) {
        console.error(error);
    }
};
getDevise();

const convertToPrice = (prixXOF) => {
    // Remplacez 655 par le taux de conversion de XOF à EUR
    const tauxConversion = taux.value;
    const prixEUR = prixXOF / tauxConversion;
    // Formatez le prix avec deux décimales
    if (local == "fr") {
        return new Intl.NumberFormat("fr-FR", {
            style: "currency",
            currency: "EUR",
        }).format(prixEUR.toFixed(2));
    } else if (local == "en") {
        return new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: "USD",
        }).format(prixEUR.toFixed(2));
    }
};
onMounted(() => {
    /*Product Details*/
    var productDetails = function () {
        $(".product-image-slider").slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            fade: false,
            asNavFor: ".slider-nav-thumbnails",
        });

        $(".slider-nav-thumbnails").slick({
            slidesToShow: 5,
            slidesToScroll: 1,
            asNavFor: ".product-image-slider",
            dots: false,
            focusOnSelect: true,
            prevArrow:
                '<button type="button" class="slick-prev"><i class="fi-rs-angle-left"></i></button>',
            nextArrow:
                '<button type="button" class="slick-next"><i class="fi-rs-angle-right"></i></button>',
        });

        // Remove active class from all thumbnail slides
        $(".slider-nav-thumbnails .slick-slide").removeClass("slick-active");

        // Set active class to first thumbnail slides
        $(".slider-nav-thumbnails .slick-slide").eq(0).addClass("slick-active");

        // On before slide change match active thumbnail to current slide
        $(".product-image-slider").on(
            "beforeChange",
            function (event, slick, currentSlide, nextSlide) {
                var mySlideNumber = nextSlide;
                $(".slider-nav-thumbnails .slick-slide").removeClass(
                    "slick-active"
                );
                $(".slider-nav-thumbnails .slick-slide")
                    .eq(mySlideNumber)
                    .addClass("slick-active");
            }
        );

        $(".product-image-slider").on(
            "beforeChange",
            function (event, slick, currentSlide, nextSlide) {
                var img = $(slick.$slides[nextSlide]).find("img");
                $(".zoomWindowContainer,.zoomContainer").remove();
                $(img).elevateZoom({
                    zoomType: "inner",
                    cursor: "crosshair",
                    zoomWindowFadeIn: 500,
                    zoomWindowFadeOut: 750,
                });
            }
        );
        //Elevate Zoom
        if ($(".product-image-slider").length) {
            $(".product-image-slider .slick-active img").elevateZoom({
                zoomType: "inner",
                cursor: "crosshair",
                zoomWindowFadeIn: 500,
                zoomWindowFadeOut: 750,
            });
        }
        //Filter color/Size
        $(".list-filter").each(function () {
            $(this)
                .find("a")
                .on("click", function (event) {
                    event.preventDefault();
                    $(this).parent().siblings().removeClass("active");
                    $(this).parent().toggleClass("active");
                    $(this)
                        .parents(".attr-detail")
                        .find(".current-size")
                        .text($(this).text());
                    $(this)
                        .parents(".attr-detail")
                        .find(".current-color")
                        .text($(this).attr("data-color"));
                });
        });
        //Qty Up-Down
        $(".detail-qty").each(function () {
            var qtyval = parseInt($(this).find(".qty-val").text(), 10);
            $(".qty-up").on("click", function (event) {
                event.preventDefault();
                qtyval = qtyval + 1;
                $(this).prev().text(qtyval);
            });
            $(".qty-down").on("click", function (event) {
                event.preventDefault();
                qtyval = qtyval - 1;
                if (qtyval > 1) {
                    $(this).next().text(qtyval);
                } else {
                    qtyval = 1;
                    $(this).next().text(qtyval);
                }
            });
        });

        $(".dropdown-menu .cart_list").on("click", function (event) {
            event.stopPropagation();
        });
    };
    /* WOW active */
    new WOW().init();

    //Load functions
    $(document).ready(function () {
        productDetails();
    });
});
</script>
<template>
    <Layout>
        <section class="mt-50 mb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">
                        <div class="product-detail accordion-detail mb-4">
                            <div class="row mb-50">
                                <div class="col-md-6 col-sm-12 col-xs-12">
                                    <div class="detail-gallery">
                                        <span class="zoom-icon"
                                            ><i class="fi-rs-search"></i
                                        ></span>
                                        <!-- MAIN SLIDES -->
                                        <div class="product-image-slider">
                                            <figure class="border-radius-10">
                                                <img
                                                    v-bind:src="'/assets/imgs/shop/product-2-2.jpg'"
                                                    alt="product image"
                                                />
                                            </figure>
                                        </div>
                                        <!-- THUMBNAILS -->
                                        <div
                                            class="slider-nav-thumbnails pl-15 pr-15"
                                        >
                                            <div
                                                v-for="row in product.images"
                                                :key="row"
                                            >
                                                <img
                                                    v-bind:src="'/assets/imgs/shop/thumbnail-7.jpg'"
                                                    alt="product image"
                                                />
                                            </div>
                                        </div>
                                    </div>
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
                                                    ><span class="text-brand">{{
                                                        convertToPrice(
                                                            product.prix
                                                        )
                                                    }}</span>
                                                </ins>
                                                <ins
                                                    ><span
                                                        class="old-price font-md ml-15"
                                                        >$200.00</span
                                                    ></ins
                                                >
                                                <span
                                                    class="save-price font-md color3 ml-15"
                                                    >25% Off</span
                                                >
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
                                            >Description</a
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

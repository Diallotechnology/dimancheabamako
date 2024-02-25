<script setup>
import Layout from "@/Shared/Layout.vue";
import { Link } from "@inertiajs/vue3";
import { Price_euro, AddToCard } from "@/helper";
import { onMounted } from "vue";
import Cart from "@/Shared/Cart.vue";
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
                        <div class="product-detail accordion-detail">
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
                                                        Price_euro.format(
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
                                                                item.id
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

                                    <li class="nav-item">
                                        <a
                                            class="nav-link"
                                            id="Reviews-tab"
                                            data-bs-toggle="tab"
                                            href="#Reviews"
                                            >Commentaire (3)</a
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

                                    <div class="tab-pane fade" id="Reviews">
                                        <!--Comments-->
                                        <div class="comments-area">
                                            <div class="row">
                                                <div class="col-lg-8">
                                                    <h4 class="mb-30">
                                                        Customer questions &
                                                        answers
                                                    </h4>
                                                    <div class="comment-list">
                                                        <div
                                                            class="single-comment justify-content-between d-flex"
                                                        >
                                                            <div
                                                                class="user justify-content-between d-flex"
                                                            >
                                                                <div
                                                                    class="thumb text-center"
                                                                >
                                                                    <img
                                                                        src="assets/imgs/page/avatar-6.jpg"
                                                                        alt=""
                                                                    />
                                                                    <h6>
                                                                        <a
                                                                            href="#"
                                                                            >Jacky
                                                                            Chan</a
                                                                        >
                                                                    </h6>
                                                                    <p
                                                                        class="font-xxs"
                                                                    >
                                                                        Since
                                                                        2012
                                                                    </p>
                                                                </div>
                                                                <div
                                                                    class="desc"
                                                                >
                                                                    <div
                                                                        class="product-rate d-inline-block"
                                                                    >
                                                                        <div
                                                                            class="product-rating"
                                                                            style="
                                                                                width: 90%;
                                                                            "
                                                                        ></div>
                                                                    </div>
                                                                    <p>
                                                                        Thank
                                                                        you very
                                                                        fast
                                                                        shipping
                                                                        from
                                                                        Poland
                                                                        only
                                                                        3days.
                                                                    </p>
                                                                    <div
                                                                        class="d-flex justify-content-between"
                                                                    >
                                                                        <div
                                                                            class="d-flex align-items-center"
                                                                        >
                                                                            <p
                                                                                class="font-xs mr-30"
                                                                            >
                                                                                December
                                                                                4,
                                                                                2020
                                                                                at
                                                                                3:12
                                                                                pm
                                                                            </p>
                                                                            <a
                                                                                href="#"
                                                                                class="text-brand btn-reply"
                                                                                >Reply
                                                                                <i
                                                                                    class="fi-rs-arrow-right"
                                                                                ></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--single-comment -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--comment form-->
                                        <div class="comment-form">
                                            <h4 class="mb-15">Add a review</h4>
                                            <div
                                                class="product-rate d-inline-block mb-30"
                                            ></div>
                                            <div class="row">
                                                <div class="col-lg-8 col-md-12">
                                                    <form
                                                        class="form-contact comment_form"
                                                        action="#"
                                                        id="commentForm"
                                                    >
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div
                                                                    class="form-group"
                                                                >
                                                                    <textarea
                                                                        class="form-control w-100"
                                                                        name="comment"
                                                                        id="comment"
                                                                        cols="30"
                                                                        rows="9"
                                                                        placeholder="Write Comment"
                                                                    ></textarea>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="col-sm-6"
                                                            >
                                                                <div
                                                                    class="form-group"
                                                                >
                                                                    <input
                                                                        class="form-control"
                                                                        name="name"
                                                                        id="name"
                                                                        type="text"
                                                                        placeholder="Name"
                                                                    />
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="col-sm-6"
                                                            >
                                                                <div
                                                                    class="form-group"
                                                                >
                                                                    <input
                                                                        class="form-control"
                                                                        name="email"
                                                                        id="email"
                                                                        type="email"
                                                                        placeholder="Email"
                                                                    />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <button
                                                                type="submit"
                                                                class="button button-contactForm"
                                                            >
                                                                Valider
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <h3 class="section-title style-1 mb-30">
                            Related products
                        </h3>
                    </div>
                    <div class="col-md-12">
                        <div class="row related-products">
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

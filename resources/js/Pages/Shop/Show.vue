<script setup>
import Layout from "@/Shared/Layout.vue";
import { Link } from "@inertiajs/vue3";
import notify, { Price_format } from "@/notifications";
import { onMounted } from "vue";
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
                                                        Price_format.format(
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
                                                {{ product.description }}
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
                                                <li class="mb-10">
                                                    <i
                                                        class="fi-rs-crown mr-5"
                                                    ></i
                                                    >Poids: {{ product.poids }}
                                                </li>
                                                <li>
                                                    En stock:<span
                                                        class="in-stock text-success ml-5"
                                                        >8 Exemplaire</span
                                                    >
                                                </li>
                                                <li class="mb-10">
                                                    <i
                                                        class="fi-rs-refresh mr-5"
                                                    ></i>
                                                    30 Day Return Policy
                                                </li>
                                            </ul>
                                        </div>

                                        <div
                                            class="bt-1 border-color-1 mt-30 mb-30"
                                        ></div>
                                        <div class="detail-extralink">
                                            <div
                                                class="detail-qty border radius"
                                            >
                                                <a href="#" class="qty-down"
                                                    ><i
                                                        class="fi-rs-angle-small-down"
                                                    ></i
                                                ></a>
                                                <span class="qty-val">1</span>
                                                <a href="#" class="qty-up"
                                                    ><i
                                                        class="fi-rs-angle-small-up"
                                                    ></i
                                                ></a>
                                            </div>
                                            <div class="product-extra-link2">
                                                <button
                                                    type="submit"
                                                    class="button button-add-to-cart"
                                                >
                                                    Acheté
                                                </button>
                                                <a
                                                    aria-label="Add To Wishlist"
                                                    class="action-btn hover-up"
                                                    href="shop-wishlist.html"
                                                    ><i class="fi-rs-heart"></i
                                                ></a>
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
                                                                        src="assets/imgs/page/avatar-7.jpg"
                                                                        alt=""
                                                                    />
                                                                    <h6>
                                                                        <a
                                                                            href="#"
                                                                            >Ana
                                                                            Rosie</a
                                                                        >
                                                                    </h6>
                                                                    <p
                                                                        class="font-xxs"
                                                                    >
                                                                        Since
                                                                        2008
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
                                                                        Great
                                                                        low
                                                                        price
                                                                        and
                                                                        works
                                                                        well.
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
                                                                        src="assets/imgs/page/avatar-8.jpg"
                                                                        alt=""
                                                                    />
                                                                    <h6>
                                                                        <a
                                                                            href="#"
                                                                            >Steven
                                                                            Keny</a
                                                                        >
                                                                    </h6>
                                                                    <p
                                                                        class="font-xxs"
                                                                    >
                                                                        Since
                                                                        2010
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
                                                                        Authentic
                                                                        and
                                                                        Beautiful,
                                                                        Love
                                                                        these
                                                                        way more
                                                                        than
                                                                        ever
                                                                        expected
                                                                        They are
                                                                        Great
                                                                        earphones
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
                                                <div class="col-lg-4">
                                                    <h4 class="mb-30">
                                                        Customer reviews
                                                    </h4>
                                                    <div class="d-flex mb-30">
                                                        <div
                                                            class="product-rate d-inline-block mr-15"
                                                        >
                                                            <div
                                                                class="product-rating"
                                                                style="
                                                                    width: 90%;
                                                                "
                                                            ></div>
                                                        </div>
                                                        <h6>4.8 out of 5</h6>
                                                    </div>
                                                    <div class="progress">
                                                        <span>5 star</span>
                                                        <div
                                                            class="progress-bar"
                                                            role="progressbar"
                                                            style="width: 50%"
                                                            aria-valuenow="50"
                                                            aria-valuemin="0"
                                                            aria-valuemax="100"
                                                        >
                                                            50%
                                                        </div>
                                                    </div>
                                                    <div class="progress">
                                                        <span>4 star</span>
                                                        <div
                                                            class="progress-bar"
                                                            role="progressbar"
                                                            style="width: 25%"
                                                            aria-valuenow="25"
                                                            aria-valuemin="0"
                                                            aria-valuemax="100"
                                                        >
                                                            25%
                                                        </div>
                                                    </div>
                                                    <div class="progress">
                                                        <span>3 star</span>
                                                        <div
                                                            class="progress-bar"
                                                            role="progressbar"
                                                            style="width: 45%"
                                                            aria-valuenow="45"
                                                            aria-valuemin="0"
                                                            aria-valuemax="100"
                                                        >
                                                            45%
                                                        </div>
                                                    </div>
                                                    <div class="progress">
                                                        <span>2 star</span>
                                                        <div
                                                            class="progress-bar"
                                                            role="progressbar"
                                                            style="width: 65%"
                                                            aria-valuenow="65"
                                                            aria-valuemin="0"
                                                            aria-valuemax="100"
                                                        >
                                                            65%
                                                        </div>
                                                    </div>
                                                    <div class="progress mb-30">
                                                        <span>1 star</span>
                                                        <div
                                                            class="progress-bar"
                                                            role="progressbar"
                                                            style="width: 85%"
                                                            aria-valuenow="85"
                                                            aria-valuemin="0"
                                                            aria-valuemax="100"
                                                        >
                                                            85%
                                                        </div>
                                                    </div>
                                                    <a
                                                        href="#"
                                                        class="font-xs text-muted"
                                                        >How are ratings
                                                        calculated?</a
                                                    >
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
                                                            <div class="col-12">
                                                                <div
                                                                    class="form-group"
                                                                >
                                                                    <input
                                                                        class="form-control"
                                                                        name="website"
                                                                        id="website"
                                                                        type="text"
                                                                        placeholder="Website"
                                                                    />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <button
                                                                type="submit"
                                                                class="button button-contactForm"
                                                            >
                                                                Submit Review
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-60">
                                <div class="col-12">
                                    <h3 class="section-title style-1 mb-30">
                                        Related products
                                    </h3>
                                </div>
                                <div class="col-12">
                                    <div class="row related-products">
                                        <div
                                            v-for="item in rows"
                                            :key="item"
                                            class="col-lg-3 col-md-4 col-12 col-sm-6"
                                        >
                                            <div
                                                class="product-cart-wrap small hover-up"
                                            >
                                                <div
                                                    class="product-img-action-wrap"
                                                >
                                                    <div
                                                        class="product-img product-img-zoom"
                                                    >
                                                        <a
                                                            href="shop-product-right.html"
                                                            tabindex="0"
                                                        >
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
                                                    <div
                                                        class="product-action-1"
                                                    >
                                                        <a
                                                            aria-label="Quick view"
                                                            class="action-btn small hover-up"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#quickViewModal
"
                                                            ><i
                                                                class="fi-rs-eye"
                                                            ></i
                                                        ></a>
                                                        <a
                                                            aria-label="Add To Wishlist"
                                                            class="action-btn small hover-up"
                                                            href="shop-wishlist.html"
                                                            tabindex="0"
                                                            ><i
                                                                class="fi-rs-heart"
                                                            ></i
                                                        ></a>
                                                    </div>
                                                    <div
                                                        class="product-badges product-badges-position product-badges-mrg"
                                                    >
                                                        <span class="hot"
                                                            >Hot</span
                                                        >
                                                    </div>
                                                </div>
                                                <div
                                                    class="product-content-wrap"
                                                >
                                                    <h2>
                                                        <a
                                                            href="shop-product-right.html"
                                                            tabindex="0"
                                                            >Ulstra Bass
                                                            Headphone</a
                                                        >
                                                    </h2>
                                                    <div
                                                        class="rating-result"
                                                        title="90%"
                                                    >
                                                        <span> </span>
                                                    </div>
                                                    <div class="product-price">
                                                        <span>$238.85 </span>
                                                        <span class="old-price"
                                                            >$245.8</span
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 primary-sidebar sticky-sidebar">
                        <div class="widget-category mb-30">
                            <h5
                                class="section-title style-1 mb-30 wow fadeIn animated"
                            >
                                Category
                            </h5>
                            <ul class="categories">
                                <li>
                                    <a href="shop-grid-right.html"
                                        >Shoes & Bags</a
                                    >
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </Layout>
</template>

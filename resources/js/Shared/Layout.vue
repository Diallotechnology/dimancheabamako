<template>
    <Nav />

    <main class="main">
        <slot></slot>
    </main>
    <footer class="main">
        <section class="section-padding footer-mid">
            <div class="container pt-15 pb-20">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="widget-about font-md mb-md-5 mb-lg-0">
                            <div class="logo logo-width-1 wow fadeIn animated">
                                <a href=""
                                    ><img
                                        v-bind:src="'/assets/imgs/theme/logo.svg'"
                                        alt="logo"
                                /></a>
                            </div>
                            <h5
                                class="mt-20 mb-10 fw-600 text-grey-4 wow fadeIn animated"
                            >
                                Contact
                            </h5>
                            <p class="wow fadeIn animated">
                                <strong>Address: </strong>562 Wellington Road,
                                Street 32, San Francisco
                            </p>
                            <p class="wow fadeIn animated">
                                <strong>Phone: </strong>+01 2222 365 /(+91) 01
                                2345 6789
                            </p>
                            <p class="wow fadeIn animated">
                                <strong>Hours: </strong>10:00 - 18:00, Mon - Sat
                            </p>
                            <h5
                                class="mb-10 mt-30 fw-600 text-grey-4 wow fadeIn animated"
                            >
                                Follow Us
                            </h5>
                            <div
                                class="mobile-social-icon wow fadeIn animated mb-sm-5 mb-md-0"
                            >
                                <a href="#"
                                    ><img
                                        v-bind:src="'/assets/imgs/theme/icons/icon-facebook.svg'"
                                        alt=""
                                /></a>
                                <a href="#"
                                    ><img
                                        v-bind:src="'/assets/imgs/theme/icons/icon-twitter.svg'"
                                        alt=""
                                /></a>
                                <a href="#"
                                    ><img
                                        v-bind:src="'/assets/imgs/theme/icons/icon-instagram.svg'"
                                        alt=""
                                /></a>
                                <a href="#"
                                    ><img
                                        v-bind:src="'/assets/imgs/theme/icons/icon-pinterest.svg'"
                                        alt=""
                                /></a>
                                <a href="#"
                                    ><img
                                        v-bind:src="'/assets/imgs/theme/icons/icon-youtube.svg'"
                                        alt=""
                                /></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <h5 class="widget-title wow fadeIn animated">About</h5>
                        <ul
                            class="footer-list wow fadeIn animated mb-sm-5 mb-md-0"
                        >
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Delivery Information</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms &amp; Conditions</a></li>
                            <li><a href="#">Contact Us</a></li>
                            <li><a href="#">Support Center</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-3">
                        <h5 class="widget-title wow fadeIn animated">
                            My Account
                        </h5>
                        <ul class="footer-list wow fadeIn animated">
                            <li><a href="#">Sign In</a></li>
                            <li><a href="#">View Cart</a></li>
                            <li><a href="#">My Wishlist</a></li>
                            <li><a href="#">Track My Order</a></li>
                            <li><a href="#">Help</a></li>
                            <li><a href="#">Order</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4">
                        <h5 class="widget-title wow fadeIn animated">
                            Install App
                        </h5>
                        <div class="row">
                            <div class="col-md-4 col-lg-12 mt-md-3 mt-lg-0">
                                <p class="mb-20 wow fadeIn animated">
                                    Secured Payment Gateways
                                </p>
                                <img
                                    class="wow fadeIn animated"
                                    v-bind:src="'/assets/imgs/theme/payment-method.png'"
                                    alt=""
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="container pb-20 wow fadeIn animated">
            <div class="row">
                <div class="col-12 mb-20">
                    <div class="footer-bottom"></div>
                </div>
                <div class="col-lg-6">
                    <p class="float-md-left font-sm text-muted mb-0">
                        &copy; 2022, <strong class="text-brand">Evara</strong> -
                        HTML Ecommerce Template
                    </p>
                </div>
                <div class="col-lg-6">
                    <p class="text-lg-end text-start font-sm text-muted mb-0">
                        All rights reserved
                    </p>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import Nav from "@/Shared/Nav.vue";
import { onMounted } from "vue";
onMounted(() => {
    /*====== Sidebar menu Active ======*/
    function mobileHeaderActive() {
        var navbarTrigger = $(".burger-icon"),
            endTrigger = $(".mobile-menu-close"),
            container = $(".mobile-header-active"),
            wrapper4 = $("body");

        wrapper4.prepend('<div class="body-overlay-1"></div>');

        navbarTrigger.on("click", function (e) {
            e.preventDefault();
            container.addClass("sidebar-visible");
            wrapper4.addClass("mobile-menu-active");
        });

        endTrigger.on("click", function () {
            container.removeClass("sidebar-visible");
            wrapper4.removeClass("mobile-menu-active");
        });

        $(".body-overlay-1").on("click", function () {
            container.removeClass("sidebar-visible");
            wrapper4.removeClass("mobile-menu-active");
        });
    }
    mobileHeaderActive();
    /*-----------------------
        Magnific Popup
    ------------------------*/
    $(".img-popup").magnificPopup({
        type: "image",
        gallery: {
            enabled: true,
        },
    });

    $(".btn-close").on("click", function (e) {
        $(".zoomContainer").remove();
    });
    $("#quickViewModal").on("show.bs.modal", function (e) {
        $(document).click(function (e) {
            var modalDialog = $(".modal-dialog");
            if (
                !modalDialog.is(e.target) &&
                modalDialog.has(e.target).length === 0
            ) {
                $(".zoomContainer").remove();
            }
        });
    });

    /*---------------------
        Select active
    --------------------- */
    $(".select-active").select2();

    /*--- Mobile demo active ----*/
    var demo = $(".tm-demo-options-wrapper");
    $(".view-demo-btn-active").on("click", function (e) {
        e.preventDefault();
        demo.toggleClass("demo-open");
    });

    /*-----More Menu Open----*/
    $(".more_slide_open").slideUp();
    $(".more_categories").on("click", function () {
        $(this).toggleClass("show");
        $(".more_slide_open").slideToggle();
    });

    $(".modal").on("shown.bs.modal", function (e) {
        $(".product-image-slider").slick("setPosition");
        $(".slider-nav-thumbnails").slick("setPosition");
        $(".product-image-slider .slick-active img").elevateZoom({
            zoomType: "inner",
            cursor: "crosshair",
            zoomWindowFadeIn: 500,
            zoomWindowFadeOut: 750,
        });
    });
});
</script>

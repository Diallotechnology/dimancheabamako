<template>
    <Nav />
    <!-- Quick view -->
    <div
        class="modal fade custom-modal"
        id="quickViewModal"
        tabindex="-1"
        aria-labelledby="quickViewModalLabel"
        aria-hidden="true"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
                <div class="modal-body">
                    <div class="cart-action text-center m-3">
                        <h4 class="my-3">
                            Produit ajouter au panier avec success!
                        </h4>
                        <Link
                            class="btn mr-10 mb-sm-15"
                            :href="route('cart.index')"
                            @click="handleModalHidden"
                            ><i class="fi-rs-shuffle mr-10"></i>Finaliser ma
                            commande</Link
                        >
                        <Link
                            class="btn"
                            aria-label="Close"
                            :href="route('home')"
                            @click="handleModalHidden"
                            ><i class="fi-rs-shopping-bag mr-10"></i>Continue
                            Shopping</Link
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
    <main class="main">
        <slot></slot>
    </main>
    <footer class="main">
        <section class="section-padding footer-mid">
            <div class="container pt-15 pb-20">
                <div class="row">
                    <div class="col-md-6">
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
                                <strong>Address: </strong>Bamako, ACI 2000 près
                                de la Place Can
                            </p>
                            <p class="wow fadeIn animated">
                                <strong>Phone: </strong>+223 66 03 51 54
                            </p>
                            <p class="wow fadeIn animated">
                                <strong>Horaire: </strong>09:00 - 19:00, Lundi -
                                Samedi
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
                                        v-bind:src="'/assets/imgs/theme/icons/icon-instagram.svg'"
                                        alt=""
                                /></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 mt-md-3 mt-lg-0">
                                <p class="mb-20 wow fadeIn animated">
                                    Secured Payment Gateways
                                </p>
                                <img
                                    class="wow fadeIn animated"
                                    v-bind:src="'/assets/imgs/theme/payment-method.png'"
                                    alt="payment-method"
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
                        &copy; 2024,
                        <strong class="text-brand">Dimanche à Bamako</strong> -
                        E-commerce
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
import { handleModalHidden } from "@/helper";
import { Link } from "@inertiajs/vue3";
import { onMounted } from "vue";

onMounted(() => {
    /*====== Sidebar menu Active ======*/
    function mobileHeaderActive() {
        var navbarTrigger = document.querySelector(".burger-icon"),
            endTrigger = document.querySelector(".mobile-menu-close"),
            container = document.querySelector(".mobile-header-active"),
            wrapper4 = document.querySelector("body");

        var bodyOverlay = document.createElement("div");
        bodyOverlay.className = "body-overlay-1";
        wrapper4.insertBefore(bodyOverlay, wrapper4.firstChild);

        navbarTrigger.addEventListener("click", function (e) {
            e.preventDefault();
            container.classList.add("sidebar-visible");
            wrapper4.classList.add("mobile-menu-active");
        });

        endTrigger.addEventListener("click", function () {
            container.classList.remove("sidebar-visible");
            wrapper4.classList.remove("mobile-menu-active");
        });

        bodyOverlay.addEventListener("click", function () {
            container.classList.remove("sidebar-visible");
            wrapper4.classList.remove("mobile-menu-active");
        });
    }
    mobileHeaderActive();

    /*---------------------
        Mobile menu active
    ------------------------ */
    // Sélection des éléments du menu et des éléments du sous-menu
    var offCanvasNav = document.querySelector(".mobile-menu");
    var offCanvasNavSubMenuItems = offCanvasNav.querySelectorAll(".dropdown");

    // Ajout du bouton de bascule à chaque sous-menu
    offCanvasNavSubMenuItems.forEach(function (subMenuItem) {
        var toggleButton = document.createElement("span");
        toggleButton.className = "menu-expand";
        toggleButton.innerHTML = '<i class="fi-rs-angle-small-down"></i>';
        subMenuItem.parentNode.insertBefore(
            toggleButton,
            subMenuItem.parentNode.firstChild
        );
    });

    // Fermeture de tous les sous-menus
    offCanvasNavSubMenuItems.forEach(function (subMenuItem) {
        subMenuItem.style.display = "none";
    });

    // Gestion de l'événement de clic sur les éléments du menu
    offCanvasNav.addEventListener("click", function (event) {
        var target = event.target;

        // Vérifier si l'élément cliqué est un lien ou un bouton de bascule
        if (
            target.tagName === "A" ||
            target.classList.contains("menu-expand")
        ) {
            // Récupérer l'élément parent du lien ou du bouton de bascule
            var listItem = target.parentElement;

            // Vérifier si l'élément parent a une classe indiquant la présence d'un sous-menu
            if (
                listItem.classList.contains("menu-item-has-children") ||
                listItem.classList.contains("has-children") ||
                listItem.classList.contains("has-sub-menu")
            ) {
                event.preventDefault();

                // Vérifier si le sous-menu est déjà visible ou caché
                var subMenu = listItem.querySelector("ul");
                if (subMenu.style.display === "block") {
                    listItem.classList.remove("active");
                    subMenu.style.display = "none";
                } else {
                    listItem.classList.add("active");

                    // Fermer les autres sous-menus ouverts
                    offCanvasNavSubMenuItems.forEach(function (item) {
                        if (item !== subMenu) {
                            item.parentElement.classList.remove("active");
                            item.style.display = "none";
                        }
                    });

                    subMenu.style.display = "block";
                }
            }
        }
    });

    /*--- language currency active ----*/
    $(".mobile-language-active").on("click", function (e) {
        e.preventDefault();
        $(".lang-dropdown-active").slideToggle(900);
    });

    /*-----------------------
        Magnific Popup
    ------------------------*/

    // Magnific Popup
    document.querySelectorAll(".img-popup").forEach(function (element) {
        element.addEventListener("click", function () {
            // Initialise Magnific Popup pour chaque élément .img-popup
            if (typeof magnificPopup !== "undefined") {
                magnificPopup({
                    items: {
                        src: element.getAttribute("href"),
                    },
                    type: "image",
                    gallery: {
                        enabled: true,
                    },
                });
            }
        });
    });

    // Bouton de fermeture
    document.querySelectorAll(".btn-close").forEach(function (button) {
        button.addEventListener("click", function () {
            // Supprimer le conteneur de zoom
            var zoomContainers = document.querySelectorAll(".zoomContainer");
            zoomContainers.forEach(function (container) {
                container.parentNode.removeChild(container);
            });
        });
    });

    // Isotope alternative (par exemple Masonry)
    $(".grid").imagesLoaded(function () {
        // init Isotope
        var $grid = $(".grid").isotope({
            itemSelector: ".grid-item",
            percentPosition: true,
            layoutMode: "masonry",
            masonry: {
                // use outer width of grid-sizer for columnWidth
                columnWidth: ".grid-item",
            },
        });
    });
});
</script>

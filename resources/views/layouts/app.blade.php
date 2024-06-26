<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description"
        content="E-commerce Dimanche à Bamako - Vente de Bazin teinté, Getzner Magnum, boubou et robes prêt-à-porter, brodés, wax et accessoires pour femme. Trouvez tout ce dont vous avez besoin ici.">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="Dimanche à bamako">
    <meta itemprop="description"
        content="E-commerce vente Bazin teinté, de Getzner Magnum, de boubou et robes prêt-à-porter, des brodés, wax et des accessoires pour femme">
    <meta itemprop="image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="https://www.dimancheabamako.com">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Dimanche à bamako">
    <meta property="og:description"
        content="E-commerce vente Bazin teinté, de Getzner Magnum, de boubou et robes prêt-à-porter, des brodés, wax et des accessoires pour femme">
    <meta property="og:image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Dimanche à bamako">
    <meta name="twitter:description"
        content="E-commerce vente Bazin teinté, de Getzner Magnum, de boubou et robes prêt-à-porter, des brodés, wax et des accessoires pour femme">
    <meta name="twitter:image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/imgs/theme/favicon.svg') }}">
    <title>{{ $title ?? 'E-commerce Dimanche à Bamako - Vente de Bazin, Getzner Magnum, Boubou et Robes' }}</title>

    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>

<body>
    <style>
        .swiper {
            width: 100%;
            height: 100%;
        }

        .swiper-slide {
            text-align: center;
            font-size: 18px;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .swiper {
            width: 100%;
            height: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        .swiper-slide {
            background-size: cover;
            background-position: center;
        }

        .mySwiper2 {
            height: 80%;
            width: 100%;
        }

        .mySwiper {
            height: 20%;
            box-sizing: border-box;
            padding: 10px 0;
        }

        .mySwiper .swiper-slide {
            width: 25%;
            height: 100%;
            opacity: 0.4;
        }

        .mySwiper .swiper-slide-thumb-active {
            opacity: 1;
        }

        .swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>

    @include('layouts.nav')
    <!-- Quick view -->
    <div wire:ignore.self class="modal fade custom-modal" id="quickViewModal" tabindex="-1"
        aria-labelledby="quickViewModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="cart-action text-center m-3">
                        <h4 class="my-3">
                            {{-- {{ GoogleTranslate::trans("Produit ajouter au panier avec success!", session('locale'))
                            }} --}}
                            Produit ajouter au panier avec success!
                        </h4>
                        <a class="btn mr-10 mb-sm-15" href="{{ route('panier') }}">
                            <i class="fi-rs-shuffle mr-10"></i>
                            {{-- {{ GoogleTranslate::trans("Finaliser ma commande", session('locale')) }} --}}
                            Finaliser ma commande
                        </a>
                        <a class="btn" aria-label="Close" href="{{ route('home') }}">
                            <i class="fi-rs-shopping-bag mr-10"></i>
                            {{-- {{ GoogleTranslate::trans("Continue Shopping", session('locale')) }} --}}
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <main class="main">
        {{ $slot }}
    </main>
    <footer class="main">
        <section class="section-padding footer-mid">
            <div class="container pt-15 pb-20">
                <div class="row">
                    <div class="col-md-6">
                        <div class="widget-about font-md mb-md-5 mb-lg-0">
                            <div class="logo logo-width-1 wow fadeIn animated">
                                <a href=""><img src="{{ asset('assets/imgs/theme/logo.svg') }}" alt="logo" /></a>
                            </div>
                            <h5 class="mt-20 mb-10 fw-600 text-grey-4 wow fadeIn animated">
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
                            <h5 class="mb-10 mt-30 fw-600 text-grey-4 wow fadeIn animated">
                                Follow Us
                            </h5>
                            <div class="mobile-social-icon wow fadeIn animated mb-sm-5 mb-md-0">
                                <a href="#"><img src="{{ asset('assets/imgs/theme/icons/icon-facebook.svg') }}"
                                        alt="" /></a>

                                <a href="#"><img src="{{ asset('assets/imgs/theme/icons/icon-facebook.svg') }}"
                                        alt="" /></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-4 col-lg-12 mt-md-3 mt-lg-0">
                                <p class="mb-20 wow fadeIn animated">
                                    Secured Payment Gateways
                                </p>
                                <img class="wow fadeIn animated"
                                    src="{{ asset('assets/imgs/theme/payment-method.png') }}" alt="payment-method" />
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
                        &copy; {{ Date('Y') }},
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/slick.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/wow.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/images-loaded.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/isotope.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/scrollup.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <x-livewire-alert::scripts />
    <script>
        var swiper = new Swiper(".mySwiper", {
      spaceBetween: 10,
      slidesPerView: 4,
      freeMode: true,
      watchSlidesProgress: true,
    });
    var swiper2 = new Swiper(".mySwiper2", {
      spaceBetween: 10,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      thumbs: {
        swiper: swiper,
      },
    });
        // boostrap validation js function
(() => {
    "use strict";

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll(".needs-validation");

    // Loop over them and prevent submission
    Array.from(forms).forEach((form) => {
        form.addEventListener(
            "submit",
            (event) => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add("was-validated");
            },
            false
        );
    });
})();

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

    </script>
    {{-- <script type="text/javascript"
        src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
    </script> --}}
</body>

</html>
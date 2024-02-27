<script setup>
import { Link } from "@inertiajs/vue3";
import { onMounted, ref, onUnmounted } from "vue";
const props = defineProps({
    active: {
        type: Boolean,
    },
});

const rows = ref([]);
const cartCount = ref(0);

const updateCartCount = () => {
    try {
        axios.get("/count").then((res) => {
            cartCount.value = res.data;
        });
    } catch (error) {
        console.error(error);
    }
};

onMounted(() => {
    document.addEventListener("cart-updated", updateCartCount);
    try {
        axios.get("/getcategory").then((response) => {
            rows.value = response.data;
        });
    } catch (error) {
        console.error(error);
    }

    try {
        axios.get("/count").then((res) => {
            cartCount.value = res.data;
        });
    } catch (error) {
        console.error(error);
    }
});
onUnmounted(() => {
    document.removeEventListener("cart-updated", updateCartCount);
});
</script>
<template>
    <header class="header-area header-style-1 header-height-2">
        <div class="header-middle header-middle-ptb-1 d-none d-lg-block">
            <div class="container">
                <div class="header-wrap">
                    <div class="logo logo-width-1">
                        <Link :href="route('home')"
                            ><img
                                v-bind:src="'/assets/imgs/theme/logo.svg'"
                                alt="logo"
                        /></Link>
                    </div>
                    <div class="header-right justify-content-end">
                        <div class="header-action-right">
                            <div class="header-info header-info-right px-4">
                                <ul>
                                    <Link
                                        class="language-dropdown-active"
                                        :href="route('language', 'en')"
                                    >
                                        <i class="fi-rs-world"></i> English
                                        <i class="fi-rs-angle-small-down"></i
                                    ></Link>
                                    <!-- <ul class="language-dropdown">
                                        <li>
                                            <Link
                                                :href="route('language', 'fr')"
                                                ><img
                                                    v-bind:src="'/assets/imgs/theme/flag-fr.png'"
                                                    alt=""
                                                />Français</Link
                                            >
                                        </li>
                                    </ul> -->

                                    <li>
                                        <i class="fi-rs-user"></i>
                                        <Link :href="route('login')"
                                            >Se connecter</Link
                                        >
                                    </li>
                                    <li class="dropdown nav-item">
                                        <a
                                            class="dropdown-toggle"
                                            data-bs-toggle="dropdown"
                                            href="#"
                                            id="dropdownAccount"
                                            aria-expanded="false"
                                        >
                                            <!-- <img
                                                class="img-xs rounded-circle"
                                                v-bind:src="'/admin/assets/imgs/people/avatar2.jpg'"
                                                alt="User"
                                        /> -->
                                        </a>
                                        <div
                                            class="dropdown-menu dropdown-menu-end"
                                            aria-labelledby="dropdownAccount"
                                        >
                                            <a class="dropdown-item" href="#"
                                                ><i
                                                    class="material-icons md-perm_identity"
                                                ></i
                                                >Profil</a
                                            >

                                            <div class="dropdown-divider"></div>
                                            <Link
                                                class="dropdown-item text-danger"
                                                :href="route('logout')"
                                                method="post"
                                                as="button"
                                                ><i
                                                    class="material-icons md-exit_to_app"
                                                ></i
                                                >Deconnexion
                                            </Link>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="header-action-2">
                                <div class="header-action-icon-2">
                                    <Link
                                        class="mini-cart-icon"
                                        :href="route('cart.index')"
                                    >
                                        <img
                                            alt="Evara"
                                            v-bind:src="'/assets/imgs/theme/icons/icon-cart.svg'"
                                        />
                                        <span class="pro-count blue"
                                            >{{ cartCount }}
                                        </span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-bottom header-bottom-bg-color sticky-bar">
            <div class="container">
                <div class="header-wrap header-space-between position-relative">
                    <div class="logo logo-width-1 d-block d-lg-none">
                        <Link :href="route('home')"
                            ><img
                                v-bind:src="'/assets/imgs/theme/logo.svg'"
                                alt="logo"
                        /></Link>
                    </div>
                    <div class="header-nav d-none d-lg-flex">
                        <div
                            class="main-menu main-menu-padding-1 main-menu-lh-2 d-none d-lg-block"
                        >
                            <nav>
                                <ul>
                                    <li>
                                        <Link
                                            :class="{
                                                active: route().current('home'),
                                            }"
                                            :href="route('home')"
                                            >Accueil</Link
                                        >
                                    </li>
                                    <li>
                                        <a href=""
                                            >Categorie
                                            <i class="fi-rs-angle-down"></i
                                        ></a>
                                        <ul class="sub-menu">
                                            <li
                                                v-for="row in rows"
                                                :key="row.id"
                                            >
                                                <Link
                                                    :href="
                                                        route('shop', row.id)
                                                    "
                                                    >{{ row.nom }}</Link
                                                >
                                            </li>
                                        </ul>
                                    </li>
                                    <li>
                                        <Link :href="route('about')"
                                            >A propos</Link
                                        >
                                    </li>
                                    <li>
                                        <Link :href="route('livraison')"
                                            >Livraison</Link
                                        >
                                    </li>
                                    <li>
                                        <Link :href="route('contact')"
                                            >Contact</Link
                                        >
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>

                    <div class="hotline d-none d-lg-block">
                        <p>
                            <i class="fi-rs-headset"></i
                            ><span>Info Line</span> 1900 - 888
                        </p>
                    </div>
                    <p class="mobile-promotion">
                        Happy <span class="text-brand">Mother's Day</span>. Big
                        Sale Up to 40%
                    </p>
                    <div class="header-action-right d-block d-lg-none">
                        <div class="header-action-2">
                            <div class="header-action-icon-2">
                                <a href="shop-wishlist.html">
                                    <img
                                        alt="Evara"
                                        v-bind:src="'assets/imgs/theme/icons/icon-heart.svg'"
                                    />
                                    <span class="pro-count white">4</span>
                                </a>
                            </div>
                            <div class="header-action-icon-2">
                                <a class="mini-cart-icon" href="shop-cart.html">
                                    <img
                                        alt="Evara"
                                        v-bind:src="'/assets/imgs/theme/icons/icon-cart.svg'"
                                    />
                                    <span class="pro-count white">2</span>
                                </a>
                                <div
                                    class="cart-dropdown-wrap cart-dropdown-hm2"
                                >
                                    <ul>
                                        <li>
                                            <div class="shopping-cart-img">
                                                <a
                                                    href="shop-product-right.html"
                                                    ><img
                                                        alt="Evara"
                                                        v-bind:src="'assets/imgs/shop/thumbnail-3.jpg'"
                                                /></a>
                                            </div>
                                            <div class="shopping-cart-title">
                                                <h4>
                                                    <a
                                                        href="shop-product-right.html"
                                                        >Plain Striola Shirts</a
                                                    >
                                                </h4>
                                                <h3>
                                                    <span>1 × </span>$800.00
                                                </h3>
                                            </div>
                                            <div class="shopping-cart-delete">
                                                <a href="#"
                                                    ><i
                                                        class="fi-rs-cross-small"
                                                    ></i
                                                ></a>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="shopping-cart-img">
                                                <a
                                                    href="shop-product-right.html"
                                                    ><img
                                                        alt="Evara"
                                                        v-bind:src="'/assets/imgs/shop/thumbnail-4.jpg'"
                                                /></a>
                                            </div>
                                            <div class="shopping-cart-title">
                                                <h4>
                                                    <a
                                                        href="shop-product-right.html"
                                                        >Macbook Pro 2022</a
                                                    >
                                                </h4>
                                                <h3>
                                                    <span>1 × </span>$3500.00
                                                </h3>
                                            </div>
                                            <div class="shopping-cart-delete">
                                                <a href="#"
                                                    ><i
                                                        class="fi-rs-cross-small"
                                                    ></i
                                                ></a>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="shopping-cart-footer">
                                        <div class="shopping-cart-total">
                                            <h4>Total <span>$383.00</span></h4>
                                        </div>
                                        <div class="shopping-cart-button">
                                            <a href="shop-cart.html"
                                                >View cart</a
                                            >
                                            <a href="shop-checkout.html"
                                                >Checkout</a
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="header-action-icon-2 d-block d-lg-none">
                                <div class="burger-icon burger-icon-white">
                                    <span class="burger-icon-top"></span>
                                    <span class="burger-icon-mid"></span>
                                    <span class="burger-icon-bottom"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="mobile-header-active mobile-header-wrapper-style">
        <div class="mobile-header-wrapper-inner">
            <div class="mobile-header-top">
                <div class="mobile-header-logo">
                    <a href="index.html"
                        ><img
                            v-bind:src="'/assets/imgs/theme/logo.svg'"
                            alt="logo"
                    /></a>
                </div>
                <div
                    class="mobile-menu-close close-style-wrap close-style-position-inherit"
                >
                    <button class="close-style search-close">
                        <i class="icon-top"></i>
                        <i class="icon-bottom"></i>
                    </button>
                </div>
            </div>
            <div class="mobile-header-content-area">
                <div class="mobile-search search-style-3 mobile-header-border">
                    <form action="#">
                        <input type="text" placeholder="Search for items…" />
                        <button type="submit">
                            <i class="fi-rs-search"></i>
                        </button>
                    </form>
                </div>
                <div class="mobile-menu-wrap mobile-header-border">
                    <div class="main-categori-wrap mobile-header-border">
                        <a class="categori-button-active-2" href="#">
                            <span class="fi-rs-apps"></span> Browse Categories
                        </a>
                        <div
                            class="categori-dropdown-wrap categori-dropdown-active-small"
                        >
                            <ul>
                                <li>
                                    <a href="shop-grid-right.html"
                                        ><i class="evara-font-dress"></i>Women's
                                        Clothing</a
                                    >
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- mobile menu start -->
                    <nav>
                        <ul class="mobile-menu">
                            <li class="menu-item-has-children">
                                <span class="menu-expand"></span
                                ><a href="index.html">Home</a>
                            </li>
                            <li class="menu-item-has-children">
                                <span class="menu-expand"></span
                                ><a href="#">Language</a>
                                <ul class="dropdown">
                                    <li><a href="#">English</a></li>
                                    <li><a href="#">French</a></li>
                                    <li><a href="#">German</a></li>
                                    <li><a href="#">Spanish</a></li>
                                </ul>
                            </li>
                        </ul>
                    </nav>
                    <!-- mobile menu end -->
                </div>
                <div class="mobile-header-info-wrap mobile-header-border">
                    <div class="single-mobile-header-info mt-30">
                        <a href="page-contact.html"> Our location </a>
                    </div>
                    <div class="single-mobile-header-info">
                        <a href="page-login-register.html">Log In / Sign Up </a>
                    </div>
                    <div class="single-mobile-header-info">
                        <a href="#">(+01) - 2345 - 6789 </a>
                    </div>
                </div>
                <div class="mobile-social-icon">
                    <h5 class="mb-15 text-grey-4">Follow Us</h5>
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
    </div>
</template>

<script setup>
import Nav from "@/Layouts/Nav.vue";

import { Link, usePage } from "@inertiajs/vue3";
import { onMounted, computed, ref } from "vue";
const user = computed(() => page.props.auth.user);
const page = usePage();
onMounted(() => {
    $(".menu-item.has-submenu .menu-link").on("click", function (e) {
        e.preventDefault();
        if ($(this).next(".submenu").is(":hidden")) {
            $(this)
                .parent(".has-submenu")
                .siblings()
                .find(".submenu")
                .slideUp(200);
        }
        $(this).next(".submenu").slideToggle(200);
    });

    // mobile offnavas triggerer for generic use
    $("[data-trigger]").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var offcanvas_id = $(this).attr("data-trigger");
        $(offcanvas_id).toggleClass("show");
        $("body").toggleClass("offcanvas-active");
        $(".screen-overlay").toggleClass("show");
    });

    $(".screen-overlay, .btn-close").click(function (e) {
        $(".screen-overlay").removeClass("show");
        $(".mobile-offcanvas, .show").removeClass("show");
        $("body").removeClass("offcanvas-active");
    });

    // minimize sideber on desktop

    $(".btn-aside-minimize").on("click", function () {
        if (window.innerWidth < 768) {
            $("body").removeClass("aside-mini");
            $(".screen-overlay").removeClass("show");
            $(".navbar-aside").removeClass("show");
            $("body").removeClass("offcanvas-active");
        } else {
            // minimize sideber on desktop
            $("body").toggleClass("aside-mini");
        }
    });

    // Perfect Scrollbar
    if ($("#offcanvas_aside").length) {
        const demo = document.querySelector("#offcanvas_aside");
        const ps = new PerfectScrollbar(demo);
    }
});
</script>

<template>
    <div class="screen-overlay"></div>

    <aside class="navbar-aside" id="offcanvas_aside">
        <div class="aside-top">
            <Link class="brand-wrap" :href="route('dashboard')">
                <img
                    v-bind:src="'/assets/imgs/theme/logo.svg'"
                    class="logo"
                    alt="Logo"
                />
            </Link>
            <div>
                <button class="btn btn-icon btn-aside-minimize">
                    <i class="text-muted material-icons md-menu_open"></i>
                </button>
            </div>
        </div>

        <Nav />
    </aside>
    <main class="main-wrap">
        <header class="main-header navbar justify-content-end">
            <div class="col-nav">
                <ul class="nav">
                    <li class="nav-item">
                        <a href="#" class="requestfullscreen nav-link btn-icon"
                            ><i class="material-icons md-cast"></i
                        ></a>
                    </li>

                    <li class="dropdown nav-item">
                        <a
                            class="dropdown-toggle"
                            data-bs-toggle="dropdown"
                            href="#"
                            id="dropdownAccount"
                            aria-expanded="false"
                        >
                            <img
                                class="img-xs rounded-circle"
                                src="https://ui-avatars.com/api/?background=random&bold=true&name={{ user.name }}"
                                alt="User"
                        /></a>
                        <div
                            class="dropdown-menu dropdown-menu-end"
                            aria-labelledby="dropdownAccount"
                        >
                            <a class="dropdown-item" href="#"
                                ><i class="material-icons md-perm_identity"></i
                                >{{ user.email }}</a
                            >

                            <div class="dropdown-divider"></div>
                            <Link
                                class="dropdown-item text-danger"
                                :href="route('logout')"
                                method="post"
                                as="button"
                                ><i class="material-icons md-exit_to_app"></i
                                >Deconnexion
                            </Link>
                        </div>
                    </li>
                </ul>
            </div>
        </header>
        <section class="content-main">
            <slot />
        </section>

        <footer class="main-footer font-xs">
            <div class="row pb-30 pt-15">
                <div class="col-sm-6">©, Evara .</div>
                <div class="col-sm-6">
                    <div class="text-sm-end">All rights reserved</div>
                </div>
            </div>
        </footer>
    </main>
</template>

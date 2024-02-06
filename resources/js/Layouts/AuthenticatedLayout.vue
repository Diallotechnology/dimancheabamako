<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Nav from "@/Layouts/Nav.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="screen-overlay"></div>

    <aside class="navbar-aside" id="offcanvas_aside">
        <div class="aside-top">
            <Link class="brand-wrap" :href="route('dashboard')">
                <img
                    v-bind:src="'assets/imgs/theme/logo.svg'"
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
                <button
                    class="btn btn-icon btn-mobile me-auto"
                    data-trigger="#offcanvas_aside"
                >
                    <i class="material-icons md-apps"></i>
                </button>
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
                                v-bind:src="'/admin/assets/imgs/people/avatar2.jpg'"
                                alt="User"
                        /></a>
                        <div
                            class="dropdown-menu dropdown-menu-end"
                            aria-labelledby="dropdownAccount"
                        >
                            <a class="dropdown-item" href="#"
                                ><i class="material-icons md-perm_identity"></i
                                >Profil</a
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

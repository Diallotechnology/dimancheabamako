<template>
    <nav>
        <ul class="menu-aside">
            <NavLink :active="route().current('dashboard')">
                <Link class="menu-link" :href="route('dashboard')">
                    <i class="icon material-icons md-home"></i>
                    <span class="text">Dashboard</span>
                </Link>
            </NavLink>

            <NavLink :active="route().current('category')">
                <Link class="menu-link" :href="route('category')">
                    <i class="icon material-icons md-store"></i>
                    <span class="text">Categories</span>
                </Link>
            </NavLink>

            <NavLink :active="route().current('product')">
                <Link class="menu-link" :href="route('product')">
                    <i class="icon material-icons md-shopping_bag"></i>
                    <span class="text">Produits</span>
                </Link>
            </NavLink>

            <NavLink :active="route().current('promotion')">
                <Link class="menu-link" :href="route('promotion')">
                    <i class="icon material-icons md-monetization_on"></i>
                    <span class="text">Promotion</span>
                </Link>
            </NavLink>

            <NavLink :active="route().current('order')">
                <Link class="menu-link" :href="route('order')">
                    <i class="icon material-icons md-shopping_cart"></i>
                    <span class="text">Ventes</span>
                </Link>
            </NavLink>

            <NavLink :active="route().current('client')">
                <Link class="menu-link" :href="route('client')">
                    <i class="icon material-icons md-person"></i>
                    <span class="text">Clients</span>
                </Link>
            </NavLink>

            <NavLink :active="route().current('zone')">
                <Link class="menu-link" :href="route('zone')">
                    <i class="icon material-icons md-map"></i>
                    <span class="text">Zone</span>
                </Link>
            </NavLink>

            <NavLink :active="route().current('pays')">
                <Link class="menu-link" :href="route('pays')">
                    <i class="icon material-icons md-flag"></i>
                    <span class="text">Pays</span>
                </Link>
            </NavLink>
            <NavLink :active="route().current('poids')">
                <Link class="menu-link" :href="route('poids')">
                    <i class="icon material-icons md-power_input"></i>
                    <span class="text">Poids</span>
                </Link>
            </NavLink>
            <NavLink :active="route().current('shipping')">
                <Link class="menu-link" :href="route('shipping')">
                    <i class="icon material-icons md-directions_transit"></i>
                    <span class="text">Shipping</span>
                </Link>
            </NavLink>

            <NavLink :active="route().current('transport')">
                <Link class="menu-link" :href="route('transport')">
                    <i class="icon material-icons md-transit_enterexit"></i>
                    <span class="text">Transporteur</span>
                </Link>
            </NavLink>
            <NavLink :active="route().current('devise')">
                <Link class="menu-link" :href="route('devise')">
                    <i class="icon material-icons md-monetization_on"></i>
                    <span class="text">Devise</span>
                </Link>
            </NavLink>

            <li
                class="menu-item has-submenu"
                :class="{
                    active:
                        route().current('user')
                        | route().current('user.client'),
                }"
            >
                <a class="menu-link" href="">
                    <i class="icon material-icons md-person"></i>
                    <span class="text">Utilisateurs</span>
                </a>
                <div class="submenu" style="display: none">
                    <Link
                        :href="route('user.client')"
                        :class="{
                            active: route().current('user.client'),
                        }"
                    >
                        Client
                    </Link>
                    <Link
                        :href="route('user')"
                        :class="{
                            active: route().current('user'),
                        }"
                    >
                        Administration
                    </Link>
                </div>
            </li>
            <NavLink :active="route().current('slide')">
                <Link class="menu-link" :href="route('slide')">
                    <i class="icon material-icons md-image"></i>
                    <span class="text">Slide</span>
                </Link>
            </NavLink>
            <NavLink :active="route().current('maintenance')">
                <Link class="menu-link" :href="route('maintenance')">
                    <i class="icon material-icons md-settings_applications"></i>
                    <span class="text">Mode maintenance</span>
                </Link>
            </NavLink>
        </ul>
    </nav>
</template>
<script setup>
import NavLink from "@/Components/NavLink.vue";
import { Link } from "@inertiajs/vue3";
</script>

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

            <NavLink :active="route().current('ville')">
                <Link class="menu-link" :href="route('ville')">
                    <i class="icon material-icons md-flag"></i>
                    <span class="text">Ville</span>
                </Link>
            </NavLink>

            <NavLink :active="route().current('transport')">
                <Link class="menu-link" :href="route('transport')">
                    <i class="icon material-icons md-transit_enterexit"></i>
                    <span class="text">Transporteur et Livraison</span>
                </Link>
            </NavLink>

            <NavLink :active="route().current('user')">
                <Link class="menu-link" :href="route('user')">
                    <i class="icon material-icons md-person"></i>
                    <span class="text">Utilisateurs</span>
                </Link>
            </NavLink>
        </ul>
    </nav>
    <!-- <Dropdown>
        <template #nom>
            <i class="icon material-icons md-shopping_bag"></i>
            <span class="text">Products</span>
        </template>
        <div class="submenu">
            <a href="page-products-list.html">Product List</a>
            <a href="page-products-grid.html">Product grid</a>
            <a href="page-products-grid-2.html">Product grid 2</a>
            <a href="page-categories.html">Categories</a>
        </div>
    </Dropdown> -->
</template>
<script setup>
import NavLink from "@/Components/NavLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import { Link } from "@inertiajs/vue3";
</script>

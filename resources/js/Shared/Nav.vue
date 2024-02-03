<template>
    <!-- Start Header/Navigation -->
    <nav
        class="custom-navbar navbar navbar navbar-expand-md navbar-dark bg-dark"
        arial-label="Furni navigation bar"
    >
        <div class="container">
            <Link class="navbar-brand" href="/">Shop</Link>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarsFurni"
                aria-controls="navbarsFurni"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarsFurni">
                <ul class="custom-navbar-nav navbar-nav ms-auto mb-2 mb-md-0">
                    <Link
                        :class="isUrl('') ? 'nav-link active' : 'nav-link'"
                        href="/"
                        >Home</Link
                    >
                    <Link
                        :class="
                            isUrl('contact') ? 'nav-link active' : 'nav-link'
                        "
                        href="/contact"
                        >Contact</Link
                    >
                </ul>

                <ul class="custom-navbar-cta navbar-nav mb-2 mb-md-0 ms-5">
                    <li>
                        <a class="nav-link" href="#"><img src="" /></a>
                    </li>
                    <li>
                        <a class="nav-link" href="cart.html"><img src="" /></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- End Header/Navigation -->
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
</script>

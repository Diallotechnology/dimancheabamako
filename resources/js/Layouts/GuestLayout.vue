<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Nav from "@/Shared/Nav.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <!-- <Link href="/">
        <ApplicationLogo class="" />
    </Link> -->
    <Nav />
    <div class="container mt-90">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
import Nav from "@/Shared/Nav.vue";
</script>

<template>
    <Nav />
    <div class="container mt-90">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <slot />
            </div>
        </div>
    </div>
</template>

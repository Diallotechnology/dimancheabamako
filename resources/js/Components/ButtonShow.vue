<template>
    <Link :href="href" class="btn btn-sm font-sm rounded btn-brand m-1">
        <i class="material-icons md-visibility"></i>
    </Link>
</template>
<script setup>
import { Link } from "@inertiajs/vue3";
const props = defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<script setup>
import Layout from "@/Shared/Layout.vue";
const props = defineProps({
    product: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    category: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
</script>
<template>
    <Layout> </Layout>
</template>

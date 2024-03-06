<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const props = defineProps({
    transport: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
</script>
<template>
    <AuthenticatedLayout> </AuthenticatedLayout>
</template>

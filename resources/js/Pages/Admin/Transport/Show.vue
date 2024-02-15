<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Input from "@/Components/Input.vue";
import { router, useForm, Link } from "@inertiajs/vue3";
import notify from "@/notifications";

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

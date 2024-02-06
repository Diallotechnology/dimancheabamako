<template>
    <Link :href="href" class="btn btn-sm font-sm rounded btn-brand">
        <i class="material-icons md-edit"></i>
        Edit
    </Link>
</template>
<script setup>
const props = defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

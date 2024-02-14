<script setup>
import { computed } from "vue";

const props = defineProps({
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active ? "menu-item active" : "menu-item"
);
</script>

<template>
    <li :class="classes">
        <slot />
    </li>
</template>

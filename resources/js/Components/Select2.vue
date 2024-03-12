<script setup>
const props = defineProps({
    label: {
        type: String,
        required: false,
        default: "",
    },
    message: {
        type: String,
    },
});
</script>

<template>
    <div class="mb-4">
        <div>
            <label class="text-uppercase form-label">{{ props.label }}</label>
            <slot></slot>
        </div>
        <div v-show="props.message">
            <p class="text-sm text-danger">
                {{ props.message }}
            </p>
        </div>
    </div>
</template>

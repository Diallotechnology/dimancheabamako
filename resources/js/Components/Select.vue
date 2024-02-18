<script setup>
const model = defineModel({
    type: [String, Number, Array],
    required: true,
});

const props = defineProps({
    label: {
        type: String,
        required: false,
        default: "",
    },
    tag: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
    },
});
</script>

<template>
    <div class="mb-4">
        <label class="text-uppercase form-label">{{ props.label }}</label>
        <select class="form-select" v-model="model" id="">
            <slot />
        </select>
        <div v-show="props.message">
            <p class="text-sm text-danger">
                {{ props.message }}
            </p>
        </div>
    </div>
</template>

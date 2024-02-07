<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: [String, Number],
    required: true,
});

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
        <label class="text-uppercase form-label">{{ props.label }}</label>
        <select class="form-select select-nice" v-model="model">
            <option>Selectionner</option>
            <slot />
        </select>
        <div v-show="props.message">
            <p class="text-sm text-danger">
                {{ props.message }}
            </p>
        </div>
    </div>
</template>

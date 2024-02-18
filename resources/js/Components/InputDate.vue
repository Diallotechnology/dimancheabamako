<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: [String, Number],
    required: true,
});

const props = defineProps({
    label: {
        type: String,
        required: true,
        default: "",
    },
    message: {
        type: String,
    },
});
const input = ref(null);
</script>

<template>
    <div class="mb-4">
        <label class="text-uppercase form-label"> {{ props.label }}</label>
        <input
            class="form-control"
            type="datetime-local"
            id="{{ model }}"
            v-model="model"
            ref="input"
        />
        <div v-show="props.message">
            <p class="text-sm text-danger">
                {{ props.message }}
            </p>
        </div>
    </div>
</template>

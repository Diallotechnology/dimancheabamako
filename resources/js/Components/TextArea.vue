<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: String,
    required: true,
});

const props = defineProps({
    place: {
        type: String,
        required: true,
        default: "",
    },

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
        <textarea
            rows="5"
            v-model="model"
            class="form-control"
            :placeholder="`Entrez ${props.place}`"
        ></textarea>
        <div v-show="props.message">
            <p class="text-sm text-danger">
                {{ props.message }}
            </p>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: String,
    required: true,
});

const props = defineProps({
    input_type: {
        type: String,
        required: true,
        default: "",
    },
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
const input = ref(null);
onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="mb-4">
        <label
            for="{{ model }}"
            v-if="props.label ? props.label : model"
            class="form-label"
        ></label>
        <input
            type="{{ input_type }}"
            :placeholder="`Entrez ${props.place}`"
            class="form-control"
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

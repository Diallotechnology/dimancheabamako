<script setup>
const model = defineModel({
    type: [String, Number, Object, Array],
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
        <div>
            <label class="text-uppercase form-label">{{ props.label }}</label>
            <!-- <multiselect
                :options="options"
                v-model="model"
                placeholder="Selectionner"
                slot="singleLabel"
                slot-scope="{ option }"
            >
                <template slot="singleLabel" slot-scope="{ option }"
                    ><strong>{{ data.name }}</strong> is written in<strong>
                        {{ data.language }}</strong
                    ></template
                >
            </multiselect> -->
            <select class="form-select" style="width: 100%" v-model="model">
                <slot />
            </select>
        </div>
        <div v-show="props.message">
            <p class="text-sm text-danger">
                {{ props.message }}
            </p>
        </div>
    </div>
</template>

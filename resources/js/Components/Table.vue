<script setup>
import Pagination from "./Pagination.vue";
import { defineProps } from "vue";
const props = defineProps({
    rows: {
        required: false,
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-nowrap mb-0">
                    <slot />
                </table>
                <h4 class="text-center my-3" v-if="rows.data.length === 0">
                    Aucun element
                </h4>
            </div>
            <Pagination :pagination="rows" />
        </div>
    </div>
</template>

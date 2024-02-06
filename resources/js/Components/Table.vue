<script setup>
import Pagination from "./Pagination.vue";
import { defineProps } from "vue";
import { Link } from "@inertiajs/vue3";
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
                <table id="" class="table table-hover">
                    <slot />
                </table>
            </div>
        </div>
    </div>
</template>

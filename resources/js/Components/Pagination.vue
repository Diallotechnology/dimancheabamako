<script setup>
import { Link } from "@inertiajs/vue3";
import { defineProps } from "vue";
defineProps({
    pagination: {
        required: false,
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <div
        class="pagination-area mt-15 mb-50"
        v-if="pagination.data && pagination.data.length > 0"
    >
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-start">
                <template v-for="row in pagination.links" :key="row.label">
                    <li class="page-item" :class="{ active: row.active }">
                        <Link
                            :href="row.url !== null ? row.url : ''"
                            v-html="row.label"
                            class="px-2 page-link"
                        />
                    </li>
                </template>
            </ul>
        </nav>
    </div>
</template>

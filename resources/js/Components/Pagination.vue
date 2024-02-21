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
    <!-- <div class="pagination-area mt-15 mb-50">
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-start">
                <li class="page-item active">
                    <Link class="page-link" href="">01</Link>
                </li>
                <li class="page-item">
                    <Link class="page-link" href=""
                        ><i class="material-icons md-chevron_right"></i
                    ></Link>
                </li>
            </ul>
        </nav>
    </div> -->

    <!-- <Link
                            v-if="row.label === '« Previous'"
                            :href="row.url"
                            class="px-2 page-link"
                        >

                            <i class="material-icons md-chevron_right"></i>
                        </Link>
                        <Link
                            v-else-if="row.label === 'Next &raquo;'"
                            :href="row.url"
                            class="px-2 page-link"
                        >

                            <i class="material-icons md-chevron_right"></i>
                        </Link>
                        <Link
                            v-else
                            :href="row.url"
                            class="px-2 page-link"
                            v-html="row.label"
                        ></Link> -->
    <div
        class="pagination-area mt-15 mb-50"
        v-if="pagination && pagination.length > 0"
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

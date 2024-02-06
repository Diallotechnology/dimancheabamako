<script setup>
import { Link } from "@inertiajs/vue3";
import { defineProps } from "vue";
const props = defineProps({
    links: {
        required: false,
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <div class="pagination-area mt-15 mb-50">
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
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
const props = defineProps({
    url: {
        type: String,
        required: false,
    },

    type: {
        type: String,
        required: false,
        default: "",
    },
});
</script>

<template>
    <h2 v-if="type === 'update'" class="p-4 text-center">
        Formulaire de mise à jour
    </h2>
    <form @submit.prevent="submit" enctype="multipart/form-data">
        <div class="row">
            <slot />
        </div>
        <div class="modal-footer">
            <Link v-if="type === 'update'" :href="url"> Annuler </Link>
            <button
                type="button"
                class="btn btn-danger rounded"
                data-bs-dismiss="modal"
            >
                Fermer
            </button>
            <button type="button" class="btn btn-primary rounded">
                Valider
            </button>
            <!-- :disabled="form.processing" -->
        </div>
    </form>
</template>

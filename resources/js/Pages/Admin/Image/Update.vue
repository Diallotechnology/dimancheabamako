<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Input from "@/Components/Input.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/notifications";

const props = defineProps({
    image: {
        required: true,
        type: Object,
        default: () => ({}),
    },
});
const form = useForm({
    chemin: null,
    _method: "PUT",
});

const submit = () => {
    form.post(route("image.update", props.image.id), {
        forceFormData: true,
        onSuccess: () => {
            form.chemin = props.image.chemin;
            form.reset();
            notify("Image mise à jour avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>
<template>
    <AuthenticatedLayout>
        <div class="card">
            <h2 class="p-4 text-center">Formulaire de mise à jour</h2>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="text-uppercase form-label"
                                    >Image</label
                                >
                                <input
                                    class="form-control"
                                    @input="
                                        form.chemin = $event.target.files[0]
                                    "
                                    type="file"
                                />
                                <div v-show="form.errors.chemin">
                                    <p class="text-sm text-danger">
                                        {{ form.errors.chemin }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <progress
                        v-if="form.progress"
                        :value="form.progress.percentage"
                        max="100"
                    >
                        {{ form.progress.percentage }}%
                    </progress>
                    <div class="modal-footer">
                        <Link
                            :href="route('product')"
                            class="btn btn-danger rounded"
                            data-bs-dismiss="modal"
                        >
                            Annuler
                        </Link>
                        <button
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                            type="submit"
                            class="btn btn-primary rounded"
                        >
                            Valider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
@/helper

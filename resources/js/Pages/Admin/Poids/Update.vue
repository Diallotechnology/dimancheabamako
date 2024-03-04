<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";
const props = defineProps({
    poids: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    min: props.poids.min,
    max: props.poids.max,
});

const submit = () => {
    form.patch(route("poids.update", props.poids.id), {
        onSuccess: () => {
            form.min = props.poids.min;
            form.max = props.poids.max;
            notify("poids mise à jour avec success !", true);
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
                    <Input
                        input_type="number"
                        place="le minimum du poids"
                        label="Minimum"
                        v-model="form.min"
                        :message="form.errors.min"
                        required
                    />
                    <Input
                        input_type="number"
                        place="le maximum du poids"
                        label="Maximum"
                        v-model="form.max"
                        :message="form.errors.max"
                        required
                    />
                    <div class="modal-footer">
                        <Link
                            :href="route('poids')"
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

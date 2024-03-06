<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";
const props = defineProps({
    devise: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    type: props.devise.type,
    taux: props.devise.taux,
});

const submit = () => {
    form.patch(route("devise.update", props.devise.id), {
        onSuccess: () => {
            form.type = props.devise.type;
            form.taux = props.devise.taux;
            notify("devises mise à jour avec success !", true);
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
                    <Select
                        v-model="form.type"
                        :message="form.errors.type"
                        label="type de devise"
                    >
                        <option value="USD">Dollars (USD)</option>
                        <option value="EUR">Euro (EUR)</option>
                    </Select>
                    <Input
                        input_type="number"
                        place="le taux"
                        label="taux d'exchange"
                        v-model="form.taux"
                        :message="form.errors.taux"
                        required
                    />
                    <div class="modal-footer">
                        <Link
                            :href="route('devise')"
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

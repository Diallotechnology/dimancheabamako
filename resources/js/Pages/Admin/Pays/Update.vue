<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";

const props = defineProps({
    zone: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    country: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    pays: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    nom: props.country.nom,
    zone_id: `${props.country.zone_id}`,
});
const submit = () => {
    form.patch(route("country.update", props.country.id), {
        onSuccess: () => {
            form.nom = props.country.nom;
            form.zone_id = `${props.country.zone_id}`;
            notify("pays mise à jour avec success !", true);
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
                    <Select2 label="Nom du pays" :message="form.errors.nom">
                        <VueSelect
                            placeholder="selectionner"
                            v-model="form.nom"
                            :options="pays"
                        />
                    </Select2>
                    <Select2 label="Zone" :message="form.errors.zone_id">
                        <VueSelect
                            placeholder="selectionner"
                            v-model="form.zone_id"
                            :options="zone"
                        />
                    </Select2>
                    <div class="modal-footer">
                        <Link
                            :href="route('pays')"
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

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
    zone_id: props.country.zone_id,
});

const submit = () => {
    form.patch(route("country.update", props.country.id), {
        onSuccess: () => {
            form.nom = props.country.nom;
            form.zone_id = props.country.zone_id;
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
                    <Select
                        v-model="form.nom"
                        :message="form.errors.nom"
                        label="Nom du pays"
                    >
                        <option
                            v-for="row in pays"
                            :key="row"
                            :value="row.official_name"
                        >
                            {{ row.official_name }}
                        </option>
                    </Select>
                    <Select
                        v-model="form.zone_id"
                        :message="form.errors.zone_id"
                        label="Zone"
                    >
                        <option v-for="row in zone" :key="row" :value="row.id">
                            {{ row.nom }}
                        </option>
                    </Select>
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

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/notifications";

const props = defineProps({
    ville: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    country: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    nom: props.ville.nom,
    country_id: props.ville.country_id,
});

const submit = () => {
    form.patch(route("ville.update", props.ville.id), {
        onSuccess: () => {
            form.nom = props.ville.nom;
            form.zone_id = props.ville.zone_id;
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
                    <Input
                        input_type="text"
                        place="le nom de la ville"
                        label="nom"
                        v-model="form.nom"
                        :message="form.errors.nom"
                        required
                    />
                    <Select
                        v-model="form.country_id"
                        :message="form.errors.country_id"
                        label="Zone"
                    >
                        <option
                            v-for="row in country"
                            :key="row.id"
                            :value="row.id"
                        >
                            {{ row.nom }}
                        </option>
                    </Select>
                    <div class="modal-footer">
                        <Link
                            :href="route('ville')"
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

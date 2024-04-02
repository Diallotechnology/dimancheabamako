<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Input from "@/Components/Input.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";

const props = defineProps({
    zone: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    pays: {
        type: Object,
        default: () => ({}),
    },
});
const form = useForm({
    nom: props.zone.nom,
    pays: props.zone.countries.map((row) => `${row.nom}`),
});
const submit = () => {
    form.patch(route("zone.update", props.zone.id), {
        onSuccess: () => {
            form.nom = props.zone.nom;
            notify("zone mise à jour avec success !", true);
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
                        place="le nom de la zone"
                        label="Nom"
                        v-model="form.nom"
                        :message="form.errors.nom"
                        required
                    />

                    <div class="mb-3">
                        <Select2
                            label="Pays de livraison"
                            :message="form.errors.pays"
                        >
                            <VueSelect
                                :is-multi="true"
                                placeholder="selectionner"
                                v-model="form.pays"
                                :options="pays"
                            />
                        </Select2>
                    </div>
                    <div class="modal-footer">
                        <Link
                            :href="route('zone')"
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

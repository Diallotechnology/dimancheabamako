<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";

const props = defineProps({
    transport: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    zone: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    nom: props.transport.nom,
    zone_id: props.transport.zones.map((row) => `${row.id}`),
});

const submit = () => {
    form.patch(route("transport.update", props.transport.id), {
        onSuccess: () => {
            form.nom = props.transport.nom;
            form.zone_id = props.transport.zones.map((row) => `${row.id}`);
            notify("transporteur mise à jour avec success !", true);
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
                        place="le nom du transporteur"
                        label="Nom"
                        v-model="form.nom"
                        :message="form.errors.nom"
                        required
                    />
                    <Select2
                        label="zone de livraison"
                        :message="form.errors.zone_id"
                    >
                        <VueSelect
                            :is-multi="true"
                            placeholder="selectionner"
                            v-model="form.zone_id"
                            :options="zone"
                        />
                    </Select2>

                    <div class="modal-footer">
                        <Link
                            :href="route('transport')"
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

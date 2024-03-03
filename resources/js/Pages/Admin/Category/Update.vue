<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";
const props = defineProps({
    category: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    nom: props.category.nom,
    description: props.category.description,
});

const submit = () => {
    form.patch(route("category.update", props.category.id), {
        onSuccess: () => {
            form.nom = props.category.nom;
            form.description = props.category.description;
            notify("category mise à jour avec success !", true);
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
                        label="Nom"
                        place="le nom de la category"
                        v-model="form.nom"
                        :message="form.errors.nom"
                        required
                    />
                    <TextArea
                        place="la description courte de la categorie"
                        label="description"
                        v-model="form.description"
                        :message="form.errors.description"
                        required
                    />
                    <div class="modal-footer">
                        <Link
                            :href="route('category')"
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

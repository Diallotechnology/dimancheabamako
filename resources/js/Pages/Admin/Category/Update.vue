<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Input from "@/Components/Input.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import notify from "@/notifications";

const props = defineProps({
    category: {
        type: Object,
        default: () => ({}),
    },
});
// console.log(category);
const form = useForm({
    id: props.category.id,
    nom: props.category.nom,
});

const submit = () => {
    form.patch(route("category.update", props.category.id), {
        onSuccess: () => {
            form.nom = props.category.nom;
            notify("category mise à jour avec success !", "ok");
        },
        onError: () => {
            notify("", "fail");
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
                        place="le nom de la category"
                        v-model="form.nom"
                        :message="form.errors.nom"
                        required
                        autofocus
                        autocomplete="nom"
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

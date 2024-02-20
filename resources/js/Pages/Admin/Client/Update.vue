<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Input from "@/Components/Input.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/notifications";

const props = defineProps({
    client: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    prenom: props.client.prenom,
    nom: props.client.nom,
    contact: props.client.contact,
    email: props.client.email,
});

const submit = () => {
    form.patch(route("client.update", props.client.id), {
        onSuccess: () => {
            form.nom = props.client.nom;
            notify("client mise à jour avec success !", true);
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
                        place="le prenom de la client"
                        v-model="form.prenom"
                        :message="form.errors.prenom"
                        required
                    />
                    <Input
                        input_type="text"
                        place="le nom du client"
                        label="Nom"
                        v-model="form.nom"
                        :message="form.errors.nom"
                        required
                    />
                    <Input
                        input_type="email"
                        place="email du client"
                        label="email"
                        v-model="form.email"
                        :message="form.errors.email"
                        required
                    />
                    <Input
                        input_type="text"
                        place="contact du client"
                        label="contact"
                        v-model="form.contact"
                        :message="form.errors.contact"
                        required
                    />
                    <div class="modal-footer">
                        <Link
                            :href="route('client')"
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

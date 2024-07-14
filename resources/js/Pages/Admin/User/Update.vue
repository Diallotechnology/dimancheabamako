<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Input from "@/Components/Input.vue";
import Select from "@/Components/Select.vue";
import { useForm, Link } from "@inertiajs/vue3";
import notify from "@/helper";

const props = defineProps({
    user: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    name: props.user.name,
    role: props.user.role,
    email: props.user.email,
});

const submit = () => {
    form.patch(route("user.update", props.user.id), {
        onSuccess: () => {
            form.name = props.user.name;
            form.email = props.user.email;
            form.role = props.user.role;
            notify("utilisateur mise à jour avec success !", true);
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
                        place="le nom du client"
                        label="Username"
                        v-model="form.name"
                        :message="form.errors.name"
                        required
                    />
                    <Input
                        input_type="email"
                        place="email de l'utilisateur"
                        label="email"
                        v-model="form.email"
                        :message="form.errors.email"
                        required
                    />
                    <Select
                        v-model="form.role"
                        :message="form.errors.role"
                        label="Role (Privilège)"
                    >
                        <option value="Administrateur">
                            Administrateur (Acces a tout)
                        </option>
                        <option value="Secretaire">
                            Secretaire (Acces limité)
                        </option>
                        <option value="Client">Client</option>
                    </Select>

                    <div class="modal-footer">
                        <Link
                            :href="route('user')"
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

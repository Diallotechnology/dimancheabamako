<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import Input from "@/Components/Input.vue";
import Select from "@/Components/Select.vue";
import notify from "@/notifications";
import { ref, watch } from "vue";
const props = defineProps({
    rows: {
        type: Object,
        default: () => ({}),
    },
    filter: {
        type: Object,
        default: () => ({}),
    },
});

let search = ref(props.filter.search);
// watch(
//     search,
//     debounce((value) => {
//         router.get(
//             "/admin/user",
//             { search: value },
//             { preserveState: true, replace: true }
//         );
//     }, 600)
// );

watch(search, (value) => {
    setTimeout(() => {
        router.get(
            "/admin/category",
            { search: value },
            { preserveState: true, replace: true }
        );
    }, 600);
});
const form = useForm({
    name: "",
    email: "",
    role: "",
});

const submit = () => {
    form.post(route("user.store"), {
        onSuccess: () => {
            form.reset();
            notify("utilisateur ajouter avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>

<template>
    <Head title="Utilisateur" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">
                    Listes des utilisateurs
                </h2>
            </div>
            <div>
                <a
                    href="#"
                    class="btn btn-primary btn-sm rounded"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#exampleModal"
                >
                    <i class="material-icons md-plus md-18"></i>

                    Nouveau</a
                >
            </div>
        </div>
        <div class="card mb-4">
            <header class="card-header">
                <div class="row gx-3">
                    <div class="col-lg-4 col-md-6 me-auto">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Recherche..."
                            class="form-control"
                        />
                    </div>
                </div>
            </header>
            <Table :rows="rows">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th scope="col">Nom</th>
                        <th scope="col">email</th>
                        <th scope="col">role</th>
                        <th scope="col">etat</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows.data" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.name }}</td>
                        <td>{{ row.email }}</td>
                        <td>
                            <p v-if="row.role">Administrateur</p>
                            <p v-if="!row.role">Secretaire</p>
                        </td>
                        <td>
                            <span
                                v-if="row.etat"
                                class="badge badge-pill badge-soft-success"
                                >En ligne</span
                            >
                            <span
                                v-if="!row.etat"
                                class="badge badge-pill badge-soft-danger"
                                >Hors ligne</span
                            >
                        </td>

                        <td>{{ row.created_at }}</td>
                        <td>
                            <ButtonEdit :href="route('user.edit', row.id)" />
                            <ButtonDelete
                                :url="route('user.destroy', row.id)"
                            />
                        </td>
                    </tr>
                </tbody>
            </Table>
        </div>
        <!-- card end// -->

        <Modal name="Formulaire de nouveaux utilisateur">
            <form @submit.prevent="submit">
                <Input
                    input_type="text"
                    place="le nom du user"
                    label="nom d'utilisateur"
                    v-model="form.name"
                    :message="form.errors.name"
                    required
                />
                <Input
                    input_type="email"
                    place="email de utilisateur"
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
                    <option :value="1">Administrateur (Acces a tout)</option>
                    <option :value="0">Secretaire (Acces limité)</option>
                </Select>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-danger rounded"
                        data-bs-dismiss="modal"
                    >
                        Fermer
                    </button>
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
        </Modal>
    </AuthenticatedLayout>
</template>
@/helper

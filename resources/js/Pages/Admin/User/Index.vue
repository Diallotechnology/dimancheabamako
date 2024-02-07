<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import Input from "@/Components/Input.vue";
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

// watchDebounced(
//     search,
//     (value) => {
//         router.get(
//             "/admin/category",
//             { search: value },
//             { preserveState: true, replace: true }
//         );
//     },
//     { debounce: 500, maxWait: 1000 }
// );
watch(search, (value) => {
    router.get(
        "/admin/user",
        { search: value },
        { preserveState: true, replace: true }
    );
});
const form = useForm({
    nom: "",
});

const submit = () => {
    form.post(route("user.store"), {
        onSuccess: () => {
            form.reset();
            notify("Utilisateur ajouter avec success !");
        },
        onError: () => {
            notify("", "fail");
        },
    });
};
</script>

<template>
    <Head title="Categorie" />

    <AuthenticatedLayout>
        <section class="content-main">
            <div class="content-header">
                <div>
                    <h2 class="content-title card-title">
                        Listes des utilisateur
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
                                placeholder="Search..."
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
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Date</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in rows.data" :key="row.id">
                            <td>{{ row.id }}</td>
                            <td>{{ row.name }}</td>
                            <td>{{ row.email }}</td>
                            <td>{{ row.role }}</td>
                            <td>{{ row.created_at }}</td>
                            <td>
                                <ButtonEdit
                                    :href="route('user.edit', row.id)"
                                />
                                <ButtonDelete
                                    :url="route('user.destroy', row.id)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </Table>
            </div>
            <!-- card end// -->
        </section>
        <Modal name="Formulaire de nouvelle utilisateur">
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

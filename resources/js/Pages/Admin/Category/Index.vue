<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import Input from "@/Components/Input.vue";
import notify from "@/helper";
import { ref, watch, onMounted } from "vue";
import VueMultiselect from "vue-multiselect";
// debounce((value) => {
//     router.get(
//         "/admin/category",
//         { search: value },
//         { preserveState: true, replace: true }
//     );
// }, 600)

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
    nom: "",
    type: "",
});

const submit = () => {
    form.post(route("category.store"), {
        onSuccess: () => {
            form.reset();
            notify("Categorie ajouter avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
const data = [];
</script>

<template>
    <Head title="Categorie" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Listes des categories</h2>
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
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows.data" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.nom }}</td>
                        <td>{{ row.created_at }}</td>
                        <td>
                            <ButtonEdit
                                :href="route('category.edit', row.id)"
                            />
                            <ButtonDelete
                                :url="route('category.destroy', row.id)"
                            />
                        </td>
                    </tr>
                </tbody>
            </Table>
        </div>
        <!-- card end// -->
        <Modal name="Formulaire de nouvelle categorie">
            <form @submit.prevent="submit">
                <Input
                    input_type="text"
                    place="le nom de la category"
                    label="Nom"
                    v-model="form.nom"
                    :message="form.errors.nom"
                    required
                />
                <VueMultiselect v-model="form.type" :options="data" />

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

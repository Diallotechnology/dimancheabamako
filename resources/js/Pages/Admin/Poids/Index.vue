<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import notify from "@/helper";
import { useFilter } from "@/Composables/useFilter";
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

const { filters, resetFilters } = useFilter({ search: "" }, "/admin/poids");

const form = useForm({
    min: "",
    max: "",
});

const submit = () => {
    form.post(route("poid.store"), {
        onSuccess: () => {
            form.reset();
            notify("Poids ajouter avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>

<template>
    <Head title="Poids" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Listes des poids</h2>
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
                            v-model="filters.search"
                            type="text"
                            placeholder="Recherche..."
                            class="form-control"
                        />
                    </div>
                    <div class="col-auto">
                        <button
                            @click="resetFilters"
                            class="btn btn-danger rounded"
                        >
                            Reset<i
                                class="material-icons md-delete_forever md-18"
                            ></i>
                        </button>
                    </div>
                </div>
            </header>
            <Table :rows="rows">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th scope="col">Min</th>
                        <th scope="col">Max</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows.data" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.min }} kg</td>
                        <td>{{ row.max }} kg</td>
                        <td>{{ row.created_at }}</td>
                        <td>
                            <ButtonEdit :href="route('poid.edit', row.id)" />
                            <ButtonDelete
                                :url="route('poid.destroy', row.id)"
                            />
                        </td>
                    </tr>
                </tbody>
            </Table>
        </div>
        <!-- card end// -->
        <Modal name="Formulaire de nouvelle poids">
            <form @submit.prevent="submit">
                <Input
                    input_type="number"
                    place="le minimum du poids"
                    label="Minimum en Kg"
                    v-model="form.min"
                    :message="form.errors.min"
                    required
                />
                <Input
                    input_type="number"
                    place="le maximum du poids"
                    label="Maximum en Kg"
                    v-model="form.max"
                    :message="form.errors.max"
                    required
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

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import notify from "@/helper";
import { ref, watch } from "vue";

const props = defineProps({
    rows: {
        type: Object,
        default: () => ({}),
    },
    zone: {
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
            "/admin/transport",
            { search: value },
            { preserveState: true, replace: true }
        );
    }, 600);
});
const form = useForm({
    nom: "",
    zone_id: [],
});

const submit = () => {
    form.post(route("transport.store"), {
        onSuccess: () => {
            form.reset();
            notify("transporteur ajouter avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>

<template>
    <Head title="transporteur" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">
                    Listes des transporteurs
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
                        <th scope="col">Zones</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows.data" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td>{{ row.nom }}</td>
                        <td>
                            <p v-for="item in row.zones" :key="item.id">
                                {{ item.nom }}
                            </p>
                        </td>
                        <td>{{ row.created_at }}</td>
                        <td>
                            <ButtonEdit
                                :href="route('transport.edit', row.id)"
                            />
                            <ButtonDelete
                                :url="route('transport.destroy', row.id)"
                            />
                        </td>
                    </tr>
                </tbody>
            </Table>
        </div>
        <!-- card end// -->
        <Modal name="Formulaire de nouveaux transporteur">
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

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import Modal from "@/Components/Modal.vue";
import { ref, watch } from "vue";
import notify, { Price_format } from "@/helper";

const props = defineProps({
    rows: {
        type: Object,
        default: () => ({}),
    },
    transport: {
        type: Object,
        default: () => ({}),
    },
    poids: {
        type: Object,
        default: () => ({}),
    },
    filter: {
        type: Object,
        default: () => ({}),
    },
});
const zone = ref([]);
const getzone = async (url) => {
    await axios
        .get(url)
        .then((response) => {
            zone.value = response.data;
        })
        .catch(function (error) {
            // handle error
            console.log(error.response);
        });
};
let search = ref(props.filter.search);
watch(search, (value) => {
    setTimeout(() => {
        router.get(
            "/admin/shipping",
            { search: value },
            { preserveState: true, replace: true }
        );
    }, 600);
});
const form = useForm({
    temps: "",
    montant: "",
    poids_id: "",
    transport_id: "",
    zone_id: "",
});

const submit = () => {
    form.post(route("shipping.store"), {
        onSuccess: () => {
            form.reset();
            notify("livraison ajouter avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>

<template>
    <Head title="shipping" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Listes des livraison</h2>
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
            <Table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th scope="col">Transporteur</th>
                        <th scope="col">zone</th>
                        <th scope="col">Temps</th>
                        <th scope="col">Poids</th>
                        <th scope="col">Montant</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template
                        v-for="(group, transportName) in rows"
                        :key="transportName"
                    >
                        <tr v-for="item in group" :key="item.id">
                            <td>{{ item.id }}</td>
                            <td>{{ transportName }}</td>
                            <td>{{ item.zone.nom }}</td>
                            <td>{{ item.temps }}</td>
                            <td>
                                {{ item.poids.min }} à {{ item.poids.max }}Kg
                            </td>
                            <td>{{ Price_format.format(item.montant) }}</td>
                            <td>{{ item.created_at }}</td>
                            <td>
                                <ButtonEdit
                                    :href="route('shipping.edit', item.id)"
                                />
                                <ButtonDelete
                                    :url="route('shipping.destroy', item.id)"
                                />
                            </td>
                        </tr>
                    </template>
                </tbody>
            </Table>
        </div>
        <!-- card end// -->
        <Modal name="Formulaire de nouvelle livraison">
            <form @submit.prevent="submit">
                <Select
                    v-model="form.transport_id"
                    :message="form.errors.transport_id"
                    label="Nom du transporteur"
                    @change="
                        getzone(route('transport.zone', form.transport_id))
                    "
                >
                    <option
                        v-for="row in transport"
                        :key="row.id"
                        :value="row.id"
                    >
                        {{ row.nom }}
                    </option>
                </Select>
                <Select2 label="Nom de la zone" :message="form.errors.zone_id">
                    <VueSelect
                        placeholder="selectionner"
                        v-model="form.zone_id"
                        :options="zone"
                    />
                </Select2>

                <Select2 label="Poids" :message="form.errors.poids_id">
                    <VueSelect
                        placeholder="selectionner"
                        v-model="form.poids_id"
                        :options="poids"
                    />
                </Select2>
                <Input
                    input_type="text"
                    place="le temps du transport"
                    label="Temps (jour)"
                    v-model="form.temps"
                    :message="form.errors.temps"
                    required
                />
                <Input
                    input_type="number"
                    place="le prix du transport"
                    label="montant en CFA"
                    v-model="form.montant"
                    :message="form.errors.montant"
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

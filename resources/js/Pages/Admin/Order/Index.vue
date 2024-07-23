<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import { Price_format } from "@/helper";
import { ref, watch } from "vue";
import ButtonShow from "@/Components/ButtonShow.vue";
const props = defineProps({
    rows: {
        type: Object,
        default: () => ({}),
    },
    client: {
        type: Object,
        default: () => ({}),
    },
    filter: {
        type: Object,
        default: () => ({}),
    },
});
let search = ref(props.filter.search);
let date = ref(props.filter.date);
const Reset = () => {
    date.value = "";
    search.value = "";
};
watch(search, (value) => {
    setTimeout(() => {
        router.get(
            "/admin/order",
            { search: value },
            { preserveState: true, replace: true }
        );
    }, 600);
});
watch(date, (value) => {
    setTimeout(() => {
        router.get(
            "/admin/order",
            { date: value },
            { preserveState: true, replace: true }
        );
    }, 600);
});
</script>

<template>
    <Head title="Vente" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Listes des ventes</h2>
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
                    <div class="col-md-2 col-6">
                        <input
                            type="date"
                            value=""
                            v-model="date"
                            class="form-control"
                        />
                    </div>
                    <div class="col-auto">
                        <button @click="Reset" class="btn btn-danger rounded">
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
                        <th scope="col">Facture N°</th>
                        <th scope="col">transaction</th>
                        <th scope="col">Client</th>
                        <th scope="col">Adresse</th>
                        <th scope="col">shipping</th>
                        <th scope="col">Postal</th>
                        <th scope="col">Pays/Ville</th>
                        <th scope="col">Total</th>
                        <th scope="col">Etat</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows.data" :key="row.id">
                        <td>{{ row.id }}</td>
                        <td class="fw-bold">{{ row.reference }}</td>
                        <td class="fw-bold">
                            {{ row.trans_ref }} <br />
                            <span
                                class="badge rounded-pill alert-success text-success"
                                >{{ row.trans_state }}</span
                            >
                        </td>
                        {{
                            row.client.prenom
                        }}
                        {{
                            row.client.nom
                        }}
                        <br />
                        {{
                            row.client.email
                        }}
                        <br />
                        {{
                            row.client.contact
                        }}
                        <td>{{ row.adresse }}</td>
                        <td>
                            {{ row.transport.nom }} <br />
                            {{ row.poids }}Kg <br />{{
                                Price_format.format(row.shipping)
                            }}
                        </td>
                        <td>{{ row.postal }}</td>
                        <td>{{ row.country.nom }} <br />{{ row.ville }}</td>
                        <td>{{ Price_format.format(row.totaux) }}</td>
                        <td>
                            <span
                                class="badge rounded-pill alert-success text-success"
                            >
                                {{ row.etat }}
                            </span>
                        </td>
                        <td>{{ row.created_at }}</td>
                        <td>
                            <ButtonShow :href="route('order.show', row.id)" />
                            <!-- <ButtonEdit :href="route('order.edit', row.id)" /> -->
                            <ButtonDelete
                                :url="route('order.destroy', row.id)"
                            />
                        </td>
                    </tr>
                </tbody>
            </Table>
        </div>
        <!-- card end// -->
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import { Price_format } from "@/helper";
import { useFilter } from "@/Composables/useFilter";
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
    order_status: {
        type: Object,
        default: () => ({}),
    },
});

const { filters, resetFilters } = useFilter(
    {
        search: "",
        date: "",
        client: "",
        status: "",
    },
    "/admin/order"
);
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
                            v-model="filters.search"
                            type="text"
                            placeholder="Recherche..."
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" v-model="filters.client">
                            <option value="">Tous les clients</option>
                            <option
                                v-for="item in client"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.nom }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" v-model="filters.status">
                            <option value="">Ventes Status</option>
                            <option
                                v-for="item in order_status"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2 col-6">
                        <input
                            type="date"
                            v-model="filters.date"
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
                            <div class="d-flex gap-2">
                                <ButtonShow
                                    :href="route('order.show', row.id)"
                                />
                                <!-- <ButtonEdit :href="route('order.edit', row.id)" /> -->
                                <ButtonDelete
                                    :url="route('order.destroy', row.id)"
                                />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </Table>
        </div>
        <!-- card end// -->
    </AuthenticatedLayout>
</template>

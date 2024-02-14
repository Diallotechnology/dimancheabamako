<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import ButtonEdit from "@/Components/ButtonEdit.vue";
import ButtonDelete from "@/Components/ButtonDelete.vue";
import Table from "@/Components/Table.vue";
import Input from "@/Components/Input.vue";
import { Price_format } from "@/notifications";
import { ref, watch } from "vue";
import debounce from "lodash.debounce";
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
let etat = ref(props.filter.etat);
let date = ref(props.filter.date);
let client_id = ref(props.filter.client_id);
watch(
    search,
    debounce((value) => {
        router.get(
            "/admin/order",
            { search: value },
            { preserveState: true, replace: true }
        );
    }, 600)
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
                            v-model="search"
                            type="text"
                            placeholder="Recherche..."
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-2 col-6">
                        <input
                            v-model="date"
                            type="date"
                            value="02.05.2022"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="custom_select">
                            <select
                                class="form-select select-nice"
                                v-model="client_id"
                            >
                                <option selected>Client</option>
                                <option
                                    v-for="row in client"
                                    :key="row.id"
                                    :value="row.id"
                                >
                                    {{ row.nom }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="custom_select">
                            <!-- <Select2
                                v-model="etat"
                                :options="[{ id: 'Paid', value: 'Paid' }]"
                            /> -->
                            <!-- <select
                                class="form-select select-nice"
                                v-model="etat"
                            >
                                <option value="" selected>Etat</option>
                                <option>All</option>
                                <option>Paid</option>
                                <option>Chargeback</option>
                                <option>Refund</option>
                            </select> -->
                        </div>
                    </div>
                </div>
            </header>
            <Table :rows="rows">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th scope="col">Reference</th>
                        <th scope="col">Client</th>

                        <th scope="col">Adresse</th>
                        <th scope="col">Postal</th>
                        <th scope="col">Pays</th>
                        <th scope="col">Ville</th>
                        <th scope="col">Payment</th>
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
                        <td>{{ row.postal }}</td>
                        <td>{{ row.pays }}</td>
                        <td>{{ row.ville }}</td>
                        <td>
                            <i
                                class="material-icons md-payment font-xxl text-muted mr-5"
                            ></i>
                            {{ row.payment }}
                        </td>
                        <td>{{ Price_format.format(row.totaux) }}</td>
                        <td>{{ row.etat }}</td>
                        <td>{{ row.created_at }}</td>
                        <td>
                            <ButtonShow :href="route('order.show', row.id)" />
                            <ButtonEdit :href="route('order.edit', row.id)" />
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

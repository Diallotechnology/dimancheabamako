<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ButtonShow from "@/Components/ButtonShow.vue";
import Table from "@/Components/Table.vue";
import { Price_format } from "@/helper";

const props = defineProps({
    client: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
</script>
<template>
    <AuthenticatedLayout>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <!--  col.// -->
                    <div class="col-xl col-lg">
                        <h3>{{ client.prenom }} {{ client.nom }}</h3>
                        <p>{{ client.email }}</p>
                        <p>{{ client.contact }}</p>
                    </div>
                </div>

                <!--  row.// -->
            </div>
            <div class="row">
                <div class="col-md-12">
                    <header class="card-header">
                        <h4 class="card-title">Liste des Achats</h4>
                    </header>
                    <Table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th scope="col">Reference</th>
                                <th scope="col">Adresse</th>
                                <th scope="col">Postal</th>
                                <th scope="col">Pays</th>
                                <th scope="col">Ville</th>
                                <th scope="col">Payment</th>
                                <th scope="col">Etat</th>
                                <th scope="col">Date</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in client.orders" :key="row.id">
                                <td>{{ row.id }}</td>
                                <td class="fw-bold">{{ row.reference }}</td>
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

                                <td>{{ row.etat }}</td>
                                <td>{{ row.created_at }}</td>
                                <td>
                                    <ButtonShow
                                        :href="route('order.show', row.id)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </Table>
                </div>
            </div>
            <!--  card-body.// -->
        </div>
        <!--  card.// -->

        <!-- content-main end// -->
    </AuthenticatedLayout>
</template>

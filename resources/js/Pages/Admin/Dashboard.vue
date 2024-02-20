<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Table from "@/Components/Table.vue";
import ButtonShow from "@/Components/ButtonShow.vue";
import { Head } from "@inertiajs/vue3";
import { Price_format } from "@/helper";
const props = defineProps({
    order: {
        type: Number,
        default: () => 0,
    },
    product: {
        type: Number,
        default: () => 0,
    },
    revenu: {
        type: Number,
        default: () => 0,
    },
    categorie: {
        type: Number,
        default: () => 0,
    },
    lastorder: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Tableau de bord</h2>
                <p>Whole data about your business here</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <div class="card card-body mb-4">
                    <article class="icontext">
                        <span
                            class="icon icon-sm rounded-circle bg-primary-light"
                            ><i
                                class="text-primary material-icons md-monetization_on"
                            ></i
                        ></span>
                        <div class="text">
                            <h6 class="mb-1 card-title">Total Revenue</h6>
                            <span>{{ Price_format.format(revenu) }}</span>
                        </div>
                    </article>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-body mb-4">
                    <article class="icontext">
                        <span
                            class="icon icon-sm rounded-circle bg-success-light"
                            ><i
                                class="text-success material-icons md-local_shipping"
                            ></i
                        ></span>
                        <div class="text">
                            <h6 class="mb-1 card-title">Total Ventes</h6>
                            <span>{{ order }}</span>
                        </div>
                    </article>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-body mb-4">
                    <article class="icontext">
                        <span
                            class="icon icon-sm rounded-circle bg-warning-light"
                            ><i
                                class="text-warning material-icons md-qr_code"
                            ></i
                        ></span>
                        <div class="text">
                            <h6 class="mb-1 card-title">Total Produits</h6>
                            <span>{{ product }}</span>
                        </div>
                    </article>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card card-body mb-4">
                    <article class="icontext">
                        <span class="icon icon-sm rounded-circle bg-info-light"
                            ><i
                                class="text-info material-icons md-shopping_basket"
                            ></i
                        ></span>
                        <div class="text">
                            <h6 class="mb-1 card-title">Total Categories</h6>
                            <span>{{ categorie }}</span>
                        </div>
                    </article>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <header class="card-header">
                    <h4 class="card-title">Ventes récente</h4>
                </header>
                <Table>
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
                        <tr v-for="row in lastorder" :key="row.id">
                            <td>{{ row.id }}</td>
                            <td class="fw-bold">{{ row.reference }}</td>
                            <td>
                                {{ row.client.prenom }} {{ row.client.nom }}
                                <br />
                                {{ row.client.email }} <br />
                                {{ row.client.contact }}
                            </td>

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
                                <ButtonShow
                                    :href="route('order.show', row.id)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </Table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
@/helper

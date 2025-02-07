<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import notify, { Price_format } from "@/helper";

const props = defineProps({
    order: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    state: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const form = useForm({
    etat: props.order.etat,
});
const submit = () => {
    form.patch(route("order.update", props.order.id), {
        onSuccess: () => {
            form.etat = props.order.etat;
            notify("vente mise à jour avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>
<template>
    <Head title="Detail Vente" />
    <AuthenticatedLayout>
        <div class="content-header">
            <div>
                <h2 class="content-title card-title">Vente detail</h2>
                <p>Details de la Vente ID: {{ order.id }}</p>
            </div>
        </div>
        <div class="card">
            <header class="card-header">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-6 mb-lg-0 mb-15">
                        <span>
                            <i class="material-icons md-calendar_today"></i>
                            <b>{{ order.created_at }}</b>
                        </span>
                        <br />
                        <small>Facture N°: {{ order.reference }}</small> <br />
                        <small
                            >Transaction reference: {{ order.trans_ref }}</small
                        ><br />
                        <small
                            >Transaction status:
                            <span
                                class="badge rounded-pill alert-success text-success"
                                >{{ order.trans_state }}</span
                            ></small
                        >
                    </div>
                    <div class="col-lg-6 col-md-6 ms-auto text-md-end">
                        <form @submit.prevent="submit">
                            <select
                                class="form-select d-inline-block mb-lg-0 mb-15 mw-200"
                                v-model="form.etat"
                            >
                                <option
                                    v-for="item in props.state"
                                    :key="item"
                                    :selected="item == order.etat"
                                >
                                    {{ item }}
                                </option>
                            </select>
                            <button
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                type="submit"
                                class="btn btn-primary"
                            >
                                Valider
                            </button>
                        </form>
                        <!-- <a class="btn btn-secondary print ms-2" href="#"
                            ><i class="icon material-icons md-print"></i
                        ></a> -->
                    </div>
                </div>
            </header>
            <!-- card-header end// -->
            <div class="card-body">
                <div class="row mb-50 mt-20 order-info-wrap">
                    <div class="col-md-4">
                        <article class="icontext align-items-start">
                            <span
                                class="icon icon-sm rounded-circle bg-primary-light"
                            >
                                <i
                                    class="text-primary material-icons md-person"
                                ></i>
                            </span>
                            <div class="text">
                                <h6 class="mb-1">Client</h6>
                                <p class="mb-1">
                                    {{ order.client.prenom }}
                                    {{ order.client.nom }} <br />
                                    {{ order.client.email }} <br />
                                    {{ order.client.contact }}
                                </p>
                            </div>
                        </article>
                    </div>
                    <!-- col// -->
                    <div class="col-md-4">
                        <article class="icontext align-items-start">
                            <span
                                class="icon icon-sm rounded-circle bg-primary-light"
                            >
                                <i
                                    class="text-primary material-icons md-local_shipping"
                                ></i>
                            </span>
                            <div class="text">
                                <h6 class="mb-1">Vente info</h6>
                                <p class="mb-1">
                                    Adress: {{ order.adresse }} <br />
                                    Shipping:
                                    {{ Price_format.format(order.shipping) }}
                                    <br />
                                    Etat: {{ order.etat }}
                                </p>
                            </div>
                        </article>
                    </div>
                    <!-- col// -->
                    <div class="col-md-4">
                        <article class="icontext align-items-start">
                            <span
                                class="icon icon-sm rounded-circle bg-primary-light"
                            >
                                <i
                                    class="text-primary material-icons md-place"
                                ></i>
                            </span>
                            <div class="text">
                                <h6 class="mb-1">Livraison infos</h6>
                                <p class="mb-1">
                                    Pays: {{ order.country.nom }} <br />
                                    Poids: {{ order.poids }} <br />
                                    Ville: {{ order.ville }}<br />
                                    Postal: {{ order.postal }}
                                </p>
                            </div>
                        </article>
                    </div>
                    <!-- col// -->
                </div>
                <!-- row // -->
                <div class="row">
                    <div class="col-lg-7">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="40%">Reference</th>
                                        <th width="40%">Produit</th>
                                        <th width="20%">Prix Unitaire</th>
                                        <th width="20%">Quantité</th>
                                        <th width="20%" class="text-end">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="item in order.products"
                                        :key="item"
                                    >
                                        <td>{{ item.reference }}</td>
                                        <td>
                                            <a class="itemside" href="#">
                                                <div class="left">
                                                    <img
                                                        v-bind:src="item.cover"
                                                        width="40"
                                                        height="40"
                                                        class="img-xs"
                                                        alt="Item"
                                                    />
                                                </div>
                                                <div class="info">
                                                    {{ item.nom }}
                                                </div>
                                            </a>
                                        </td>

                                        <td>
                                            {{ Price_format.format(item.prix) }}
                                        </td>
                                        <td>{{ item.pivot.quantity }}</td>
                                        <td class="text-end">
                                            {{
                                                Price_format.format(
                                                    item.pivot.montant
                                                )
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <article class="float-end">
                                                <dl class="dlist">
                                                    <dt>Montant total:</dt>
                                                    <dd>
                                                        <p class="h5">
                                                            {{
                                                                Price_format.format(
                                                                    order.totaux
                                                                )
                                                            }}
                                                        </p>
                                                    </dd>
                                                </dl>
                                            </article>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- col// -->
                    <div class="col-lg-1"></div>
                    <div class="col-lg-4">
                        <div class="box shadow-sm bg-light">
                            <h6 class="mb-15">Commentaire</h6>
                            <p>{{ order.commentaire }}</p>
                        </div>
                    </div>
                    <!-- col// -->
                </div>
            </div>
            <!-- card-body end// -->
        </div>
        <!-- card end// -->
    </AuthenticatedLayout>
</template>

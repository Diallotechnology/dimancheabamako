<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import notify from "@/notifications";
let Price_format = new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency: "XOF",
});
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
    payment: props.order.payment,
    adresse: props.order.adresse,
    postal: props.order.postal,
    ville: props.order.ville,
    pays: props.order.pays,
});
const submit = () => {
    form.patch(route("order.update", props.order.id), {
        onSuccess: () => {
            form.etat = props.order.etat;
            form.payment = props.order.payment;
            form.adresse = props.order.adresse;
            form.postal = props.order.postal;
            form.ville = props.order.ville;
            form.pays = props.order.pays;
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
                        <small>Reference: {{ order.reference }}</small>
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
                        <a class="btn btn-secondary print ms-2" href="#"
                            ><i class="icon material-icons md-print"></i
                        ></a>
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
                                    Shipping: {{ order.adresse }} <br />
                                    Payement method: {{ order.payement }} <br />
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
                                    Pays: {{ order.pays }} <br />
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
                                        <th width="40%">Produit</th>
                                        <th width="20%">PU</th>
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
                                        <td>
                                            <a class="itemside" href="#">
                                                <div class="left">
                                                    <img
                                                        src="assets/imgs/items/1.jpg"
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
                                                <dl class="dlist">
                                                    <dt class="text-muted">
                                                        Status:
                                                    </dt>
                                                    <dd>
                                                        <span
                                                            class="badge rounded-pill alert-success text-success"
                                                            >Payment done</span
                                                        >
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
                            <h6 class="mb-15">Payment info</h6>
                            <p>
                                <img
                                    src="assets/imgs/card-brands/2.png"
                                    class="border"
                                    height="20"
                                />
                                {{ order.payment }} **** **** 4768
                                <br />
                                Contact: {{ order.client.contact }}
                            </p>
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

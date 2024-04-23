<script setup>
import Layout from "@/Shared/Layout.vue";
import notify, { cartnotify } from "@/helper";
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";

const props = defineProps({
    items: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    country: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    totalWeight: {
        type: Number,
        required: true,
        default: 0,
    },
    TotalQuantity: {
        type: Number,
        required: true,
        default: 0,
    },
    Total: {
        type: Number,
        required: true,
        default: 0,
    },
});

const qte = ref({});
const shipping = ref([]);
const trans = ref([]);
const page = usePage();
const locale = page.props.locale === "fr" ? "fr-FR" : "en-US";
const currency = page.props.locale === "fr" ? "EUR" : "USD";
const totalFormat = new Intl.NumberFormat(locale, {
    style: "currency",
    currency: currency,
});

onMounted(() => {
    for (const produitId in props.items) {
        qte.value[produitId] = ref(props.items[produitId].quantity);
    }
});

const form = useForm({
    prenom: "",
    nom: "",
    email: "",
    contact: "",
    transport_id: "",
    country_id: "",
    ville: "",
    adresse: "",
    postal: "",
    payment: "Visa",
    commentaire: "",
    password: "",
    livraison: "",
});

const getTrans = async () => {
    await axios
        .get(route("cart.trans", form.country_id))
        .then((response) => {
            if (response.data.type) {
                cartnotify(response.data.message, response.data.type);
            } else {
                trans.value = response.data;
            }
        })
        .catch(function (error) {
            // handle error
            console.log(error.response);
        });
};

const getShipping = async () => {
    await axios
        .get(route("cart.shipping", [form.country_id, form.transport_id]))
        .then((res) => {
            if (res.data.type) {
                cartnotify(res.data.message, res.data.type);
            } else {
                shipping.value = res.data;
                form.livraison = res.data.id;
                console.log(form.livraison);
            }
        })
        .catch(function (error) {
            // handle error
            console.log(error.res);
        });
};
const deleteProduct = async (url) => {
    await axios
        .delete(url)
        .then((response) => {
            cartnotify(response.data.message, response.data.type);
            router.reload();
        })
        .catch(function (error) {
            // handle error
            console.log(error.response);
        });
};
const update = async (produitId) => {
    await axios
        .get(route("cart.update", [produitId, qte.value[produitId]]))
        .then((response) => {
            if (response.data) {
                cartnotify(response.data.message, response.data.type);
            }
            console.log(response);
            router.reload();
        })
        .catch(function (error) {
            // handle error
            console.log(error.response);
        });
};
const increment = async (produitId) => {
    await axios
        .get(route("cart.update", [produitId, qte.value[produitId]++]))
        .then((response) => {
            if (response.data) {
                cartnotify(response.data.message, response.data.type);
            }
            router.reload();
        })
        .catch(function (error) {
            // handle error
            console.log(error.response);
        });
};
const decrement = async (produitId) => {
    console.log(quantity);
    await axios
        .get(route("cart.update", [produitId, qte.value[produitId]--]))
        .then((response) => {
            if (response.data) {
                cartnotify(response.data.message, response.data.type);
            }

            router.reload();
        })
        .catch(function (error) {
            // handle error
            console.log(error.response);
        });
};
const submit = () => {
    form.post(route("order.store"), {
        onSuccess: () => {
            form.reset();
            notify("Commande effectué avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>
<template>
    <Head title="Panier" />
    <Layout>
        <section class="mt-50 mb-50">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-20">
                            <h4>
                                Panier
                                {{ Object.keys(items).length }} element
                            </h4>
                        </div>
                        <div class="table-responsive order_table text-center">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>Prix unitaire</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in items" :key="item.id">
                                        <td class="image product-thumbnail">
                                            <img
                                                v-bind:src="
                                                    item.associatedModel.cover
                                                "
                                                alt="#"
                                            />
                                            <h5>
                                                {{ item.name }}
                                            </h5>
                                        </td>
                                        <td
                                            class="text-center"
                                            data-title="Stock"
                                        >
                                            <div
                                                class="border radius d-inline-flex"
                                            >
                                                <button
                                                    class="btn btn-small"
                                                    @click="decrement(item.id)"
                                                >
                                                    <i class="fi-rs-minus"></i>
                                                </button>

                                                <input
                                                    type="number"
                                                    v-model="qte[item.id]"
                                                    @change="update(item.id)"
                                                    ref="input"
                                                    autocomplete="off"
                                                    class="qty-val form-control"
                                                    style="max-width: 80px"
                                                />
                                                <button
                                                    class="btn btn-small"
                                                    @click="increment(item.id)"
                                                >
                                                    <i class="fi-rs-plus"></i>
                                                </button>
                                            </div>
                                        </td>

                                        <td>
                                            {{
                                                item.associatedModel.prix_final
                                            }}
                                        </td>
                                        <td class="action" data-title="Remove">
                                            <button
                                                @click="
                                                    deleteProduct(
                                                        route(
                                                            'cart.destroy',
                                                            item.id
                                                        )
                                                    )
                                                "
                                                class="btn-small btn-danger text-white"
                                            >
                                                <i class="fi-rs-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <h4
                                class="text-center my-5"
                                v-if="!Object.keys(items).length > 0"
                            >
                                Aucun produit dans le panier
                            </h4>
                        </div>
                    </div>
                    <div class="col-md-4 order-1">
                        <div
                            v-show="Object.keys(props.items).length > 0"
                            class="table-responsive order_table text-center sticky-top"
                        >
                            <table class="table">
                                <tr>
                                    <th>Poids Total</th>
                                    <td>
                                        <em>{{ totalWeight + " Kg" }}</em>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Livraison</th>
                                    <td>
                                        <em>{{
                                            shipping.montant_devise
                                                ? totalFormat.format(
                                                      shipping.montant_devise
                                                  )
                                                : ""
                                        }}</em>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Delai de Livraison</th>
                                    <td>
                                        <em>4 à 7 jours</em>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Quantity</th>
                                    <td>
                                        <em>{{ TotalQuantity }}</em>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td class="product-subtotal">
                                        <span class="font-xl text-brand fw-900">
                                            {{
                                                shipping.montant_devise
                                                    ? totalFormat.format(
                                                          Total +
                                                              shipping.montant_devise
                                                      )
                                                    : totalFormat.format(Total)
                                            }}</span
                                        >
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div
                        class="col-md-8"
                        v-show="Object.keys(props.items).length > 0"
                    >
                        <div class="mb-25">
                            <h4>Informations de la commande</h4>
                        </div>
                        <form method="post" @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-6">
                                    <Input
                                        input_type="text"
                                        place="votre prenom"
                                        label="preNom"
                                        v-model="form.prenom"
                                        :message="form.errors.prenom"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <Input
                                        input_type="text"
                                        place="votre nom"
                                        label="Nom"
                                        v-model="form.nom"
                                        :message="form.errors.nom"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <Input
                                        input_type="text"
                                        place="votre adresse"
                                        label="Adresse"
                                        v-model="form.adresse"
                                        :message="form.errors.adresse"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <Input
                                        input_type="text"
                                        place="votre ville"
                                        label="Ville"
                                        v-model="form.ville"
                                        :message="form.errors.ville"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <div class="mt-3">
                                        <Input
                                            input_type="text"
                                            place="votre contact"
                                            label="contact (avec l'indicatif)"
                                            v-model="form.contact"
                                            :message="form.errors.contact"
                                            required
                                        />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <Input
                                        input_type="email"
                                        place="votre email"
                                        label="email"
                                        v-model="form.email"
                                        :message="form.errors.email"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <p>
                                            NB: en premier lieu, sélectionnez un
                                            pays
                                        </p>
                                        <label class="text-uppercase form-label"
                                            >Pays de livraison</label
                                        >
                                        <select
                                            class="form-select"
                                            v-model="form.country_id"
                                            @change="getTrans()"
                                        >
                                            <option selected disabled value="">
                                                selectionner
                                            </option>
                                            <option
                                                v-for="item in country"
                                                :key="item.id"
                                                :value="item.id"
                                            >
                                                {{ item.nom }}
                                            </option>
                                        </select>

                                        <div v-show="form.errors.country_id">
                                            <p class="text-sm text-danger">
                                                {{ form.errors.country_id }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <Input
                                        input_type="text"
                                        place="votre code postal"
                                        label="Postal"
                                        v-model="form.postal"
                                        :message="form.errors.postal"
                                        required
                                    />
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="text-uppercase form-label"
                                            >Transporteur</label
                                        >
                                        <select
                                            class="form-select"
                                            v-model="form.transport_id"
                                            @change="getShipping()"
                                        >
                                            <option selected disabled value="">
                                                selectionner un transporteur
                                            </option>
                                            <option
                                                v-for="item in trans"
                                                :key="item.id"
                                                :value="item.id"
                                            >
                                                {{ item.nom }}
                                            </option>
                                        </select>

                                        <div v-show="form.errors.transport_id">
                                            <p class="text-sm text-danger">
                                                {{ form.errors.transport_id }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox">
                                    <div class="custome-checkbox">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="createaccount"
                                        />
                                        <label
                                            class="form-check-label label_info"
                                            data-bs-toggle="collapse"
                                            href="#collapsePassword"
                                            data-target="#collapsePassword"
                                            aria-controls="collapsePassword"
                                            for="createaccount"
                                            ><span
                                                >Souhaitez vous crée un compte
                                                directement?</span
                                            ></label
                                        >
                                    </div>
                                </div>
                            </div>
                            <div
                                id="collapsePassword"
                                class="form-group create-account collapse in"
                            >
                                <Input
                                    input_type="password"
                                    place="entrez votre mot de passe"
                                    label=""
                                    v-model="form.password"
                                    :message="form.errors.password"
                                />
                            </div>

                            <div class="mb-20">
                                <h5>
                                    Avez-vous des commentaire a ajouter a votre
                                    commande ?
                                </h5>
                            </div>
                            <div class="form-group mb-30">
                                <textarea
                                    v-model="form.commentaire"
                                    rows="5"
                                    placeholder="Order notes"
                                ></textarea>
                            </div>
                            <div class="col-md-4">
                                <div
                                    class="bt-1 border-color-1 mt-30 mb-30"
                                ></div>
                                <div class="payment_method">
                                    <div class="mb-25">
                                        <h5>Moyen de Paiment</h5>
                                    </div>
                                    <div class="payment_option">
                                        <div class="custome-radio">
                                            <input
                                                class="form-check-input"
                                                v-model="form.payment"
                                                type="radio"
                                                id="exampleRadios3"
                                                checked
                                            />
                                            <label
                                                class="form-check-label"
                                                for="exampleRadios3"
                                                data-bs-toggle="collapse"
                                                data-target="#bankTranfer"
                                                aria-controls="bankTranfer"
                                                >Direct Bank Transfer</label
                                            >
                                        </div>
                                    </div>
                                </div>
                                <button
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    type="submit"
                                    class="btn btn-fill-out btn-block mt-30"
                                >
                                    Valider
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </Layout>
</template>

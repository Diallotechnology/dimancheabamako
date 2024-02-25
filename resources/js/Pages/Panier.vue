<script setup>
import Layout from "@/Shared/Layout.vue";
import notify, { Price_euro, cartnotify } from "@/helper";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";

const props = defineProps({
    items: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    pays: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    transport: {
        type: Object,
        required: true,
        default: () => ({}),
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
    trans: "",
    pays: "",
    ville: "",
    adresse: "",
    postal: "",
    payment: "",
    commentaire: "",
});
const shipping = ref();
const getPays = async () => {
    await axios
        .get(route("cart.country"))
        .then((response) => {
            countrie.value = response.data;
        })
        .catch(function (error) {
            // handle error
            console.log(error.response);
        });
};
const getShipping = async () => {
    // if (form.pays.length && form.trans.length) {
    try {
        const response = await axios.get(
            route("cart.shipping", [form.pays, form.trans])
        );
        shipping.value = response.data;
        console.log(response.data);
    } catch (error) {
        console.error(error.response);
    }
    // }
};
const deleteProduct = async (url) => {
    console.log(url);
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
    form.post(route("category.store"), {
        onSuccess: () => {
            form.reset();
            notify("Categorie ajouter avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
};
</script>
<template>
    <Layout>
        <section class="mt-50 mb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mb-sm-15">
                        <div class="toggle_info">
                            <span
                                ><i class="fi-rs-user mr-10"></i
                                ><span class="text-muted"
                                    >Already have an account?</span
                                >
                                <a
                                    href="#loginform"
                                    data-bs-toggle="collapse"
                                    class="collapsed"
                                    aria-expanded="false"
                                    >Click here to login</a
                                ></span
                            >
                        </div>
                        <div
                            class="panel-collapse collapse login_form"
                            id="loginform"
                        >
                            <div class="panel-body">
                                <p class="mb-30 font-sm">
                                    If you have shopped with us before, please
                                    enter your details below. If you are a new
                                    customer, please proceed to the Billing
                                    &amp; Shipping section.
                                </p>
                                <form method="post">
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            name="email"
                                            placeholder="Username Or Email"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <input
                                            type="password"
                                            name="password"
                                            placeholder="Password"
                                        />
                                    </div>
                                    <div class="login_footer form-group">
                                        <div class="chek-form">
                                            <div class="custome-checkbox">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="checkbox"
                                                    id="remember"
                                                    value=""
                                                />
                                                <label
                                                    class="form-check-label"
                                                    for="remember"
                                                    ><span
                                                        >Remember me</span
                                                    ></label
                                                >
                                            </div>
                                        </div>
                                        <a href="#">Forgot password?</a>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-md" name="login">
                                            Log in
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="divider mt-50 mb-50"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-20">
                            <h4>panier</h4>
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
                                                src="assets/imgs/shop/product-1-1.jpg"
                                                alt="#"
                                            />
                                            <h5>
                                                {{ item.name }} {{ item.id }}
                                            </h5>
                                            <span class="product-qty"
                                                >st
                                                {{
                                                    item.associatedModel.stock
                                                }}</span
                                            >
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
                                            {{ Price_euro.format(item.price) }}
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
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="table-responsive order_table text-center">
                            <table class="table">
                                <tr>
                                    <th>Livraison</th>
                                    <td>
                                        <em>Free Shipping</em>
                                    </td>
                                </tr>
                                <tr>
                                    <th>TotalQuantity</th>
                                    <td>
                                        <em>{{ TotalQuantity }}</em>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td class="product-subtotal">
                                        <span class="font-xl text-brand fw-900">
                                            {{ Price_euro.format(Total) }}</span
                                        >
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="mb-25">
                            <h4>Billing Details</h4>
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
                                        input_type="email"
                                        place="votre email"
                                        label="email"
                                        v-model="form.email"
                                        :message="form.errors.email"
                                        required
                                    />
                                </div>
                                <div class="col-md-6">
                                    <Input
                                        input_type="text"
                                        place="votre contact"
                                        label="contact"
                                        v-model="form.contact"
                                        :message="form.errors.contact"
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
                                    <div class="mb-4">
                                        <label class="text-uppercase form-label"
                                            >Pays de livraison</label
                                        >
                                        <select
                                            class="form-select"
                                            v-model="form.pays"
                                            @change="getShipping()"
                                        >
                                            <option
                                                v-for="item in pays"
                                                :key="item.id"
                                                :value="item.id"
                                            >
                                                {{ item.nom }}
                                            </option>
                                        </select>

                                        <div v-show="form.errors.pays">
                                            <p class="text-sm text-danger">
                                                {{ form.errors.pays }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="text-uppercase form-label"
                                            >Transporteur</label
                                        >
                                        <select
                                            class="form-select"
                                            v-model="form.trans"
                                            @change="getShipping()"
                                        >
                                            <option
                                                v-for="item in transport"
                                                :key="item.id"
                                                :value="item.id"
                                            >
                                                {{ item.nom }}
                                            </option>
                                        </select>

                                        <div v-show="form.errors.trans">
                                            <p class="text-sm text-danger">
                                                {{ form.errors.trans }}
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
                                            name="checkbox"
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
                                                >Créer un compte??</span
                                            ></label
                                        >
                                    </div>
                                </div>
                            </div>
                            <div
                                id="collapsePassword"
                                class="form-group create-account collapse in"
                            >
                                <input
                                    required
                                    type="password"
                                    placeholder="entrez voter mot de passe"
                                    autocomplete="off"
                                />
                            </div>

                            <div class="mb-20">
                                <h5>Additional information</h5>
                            </div>
                            <div class="form-group mb-30">
                                <textarea
                                    v-model="form.commentaire"
                                    rows="5"
                                    placeholder="Order notes"
                                ></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <div class="bt-1 border-color-1 mt-30 mb-30"></div>
                        <div class="payment_method">
                            <div class="mb-25">
                                <h5>Payment</h5>
                            </div>
                            <div class="payment_option">
                                <div class="custome-radio">
                                    <input
                                        class="form-check-input"
                                        required=""
                                        type="radio"
                                        name="payment_option"
                                        id="exampleRadios3"
                                        checked=""
                                    />
                                    <label
                                        class="form-check-label"
                                        for="exampleRadios3"
                                        data-bs-toggle="collapse"
                                        data-target="#bankTranfer"
                                        aria-controls="bankTranfer"
                                        >Direct Bank Transfer</label
                                    >
                                    <div
                                        class="form-group collapse in"
                                        id="bankTranfer"
                                    >
                                        <p class="text-muted mt-5">
                                            There are many variations of
                                            passages of Lorem Ipsum available,
                                            but the majority have suffered
                                            alteration.
                                        </p>
                                    </div>
                                </div>

                                <div class="custome-radio">
                                    <input
                                        class="form-check-input"
                                        required=""
                                        type="radio"
                                        name="payment_option"
                                        id="exampleRadios5"
                                        checked=""
                                    />
                                    <label
                                        class="form-check-label"
                                        for="exampleRadios5"
                                        data-bs-toggle="collapse"
                                        data-target="#paypal"
                                        aria-controls="paypal"
                                        >Paypal</label
                                    >
                                    <div
                                        class="form-group collapse in"
                                        id="paypal"
                                    >
                                        <p class="text-muted mt-5">
                                            Pay via PayPal; you can pay with
                                            your credit card if you don't have a
                                            PayPal account.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="#" class="btn btn-fill-out btn-block mt-30"
                            >Valider</a
                        >
                    </div>
                </div>
            </div>
        </section>
    </Layout>
</template>

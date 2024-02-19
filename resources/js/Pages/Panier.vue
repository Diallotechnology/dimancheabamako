<script setup>
import Layout from "@/Shared/Layout.vue";
import { Price_format } from "@/notifications";
import { Head, router, useForm } from "@inertiajs/vue3";
import notify from "@/notifications";
import { ref } from "vue";
const props = defineProps({
    items: {
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

const form = useForm({
    prenom: "",
    nom: "",
    email: "",
    contact: "",
    shipping: "",
    pays: "",
    ville: "",
    adresse: "",
    postal: "",
    payment: "",
    commentaire: "",
});
const countrie = ref([]);
const getPays = () => {
    console.log(form.shipping);
    axios
        .get(route("cart.country"))
        .then((response) => {
            countrie.value = response.data;
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
                    <div class="col-md-3">
                        <div class="mb-25">
                            <h4>Billing Details</h4>
                        </div>
                        <form method="post">
                            <Input
                                input_type="text"
                                place="votre prenom"
                                label="preNom"
                                v-model="form.prenom"
                                :message="form.errors.prenom"
                                required
                            />
                            <Input
                                input_type="text"
                                place="votre nom"
                                label="Nom"
                                v-model="form.nom"
                                :message="form.errors.nom"
                                required
                            />
                            <Input
                                input_type="email"
                                place="votre email"
                                label="email"
                                v-model="form.email"
                                :message="form.errors.email"
                                required
                            />
                            <Input
                                input_type="text"
                                place="votre contact"
                                label="contact"
                                v-model="form.contact"
                                :message="form.errors.contact"
                                required
                            />
                            <!-- <Select
                                label="Zone"
                                v-model="form.shipping"
                                :data="zone"
                                :message="form.errors.shipping"
                            /> -->

                            <div class="mb-4">
                                <label class="text-uppercase form-label"
                                    >Pays de livraison</label
                                >
                                <select class="form-select" v-model="form.pays">
                                    <option
                                        v-for="item in countrie"
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
                            <div class="ship_detail">
                                <div class="form-group">
                                    <div class="chek-form">
                                        <div class="custome-checkbox">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="checkbox"
                                                id="differentaddress"
                                            />
                                            <label
                                                class="form-check-label label_info"
                                                data-bs-toggle="collapse"
                                                data-target="#collapseAddress"
                                                href="#collapseAddress"
                                                aria-controls="collapseAddress"
                                                for="differentaddress"
                                                ><span
                                                    >Ship to a different
                                                    address?</span
                                                ></label
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div
                                    id="collapseAddress"
                                    class="different_address collapse in"
                                >
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            required=""
                                            name="fname"
                                            placeholder="First name *"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            required=""
                                            name="lname"
                                            placeholder="Last name *"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <input
                                            required=""
                                            type="text"
                                            name="cname"
                                            placeholder="Company Name"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <div class="custom_select"></div>
                                    </div>
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            name="billing_address"
                                            required=""
                                            placeholder="Address *"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            name="billing_address2"
                                            required=""
                                            placeholder="Address line2"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <input
                                            required=""
                                            type="text"
                                            name="city"
                                            placeholder="City / Town *"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <input
                                            required=""
                                            type="text"
                                            name="state"
                                            placeholder="State / County *"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <input
                                            required=""
                                            type="text"
                                            name="zipcode"
                                            placeholder="Postcode / ZIP *"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="mb-20">
                                <h5>Additional information</h5>
                            </div>
                            <div class="form-group mb-30">
                                <textarea
                                    rows="5"
                                    placeholder="Order notes"
                                ></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-9">
                        <div class="mb-20">
                            <h4>panier</h4>
                        </div>
                        <div class="table-responsive order_table text-center">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="2">Produit</th>
                                        <th>Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in items" :key="item">
                                        <td class="image product-thumbnail">
                                            <img
                                                src="assets/imgs/shop/product-1-1.jpg"
                                                alt="#"
                                            />
                                        </td>
                                        <td>
                                            <h5>{{ item.name }}</h5>
                                            <span class="product-qty"
                                                >Qte {{ item.quantity }}</span
                                            >
                                        </td>
                                        <td>
                                            {{
                                                Price_format.format(item.price)
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Shipping</th>
                                        <td colspan="2">
                                            <em>Free Shipping</em>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total</th>
                                        <td
                                            colspan="2"
                                            class="product-subtotal"
                                        >
                                            <span
                                                class="font-xl text-brand fw-900"
                                            >
                                                {{
                                                    Price_format.format(Total)
                                                }}</span
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
                                        id="exampleRadios4"
                                        checked=""
                                    />
                                    <label
                                        class="form-check-label"
                                        for="exampleRadios4"
                                        data-bs-toggle="collapse"
                                        data-target="#checkPayment"
                                        aria-controls="checkPayment"
                                        >Check Payment</label
                                    >
                                    <div
                                        class="form-group collapse in"
                                        id="checkPayment"
                                    >
                                        <p class="text-muted mt-5">
                                            Please send your cheque to Store
                                            Name, Store Street, Store Town,
                                            Store State / County, Store
                                            Postcode.
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

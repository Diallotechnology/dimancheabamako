<script setup>
import Layout from "@/Shared/Layout.vue";
import Pagination from "@/Components/Pagination.vue";
import { AddToCard } from "@/helper";
import { Link, router, usePage } from "@inertiajs/vue3";
import { onMounted, watch, ref } from "vue";
const props = defineProps({
    rows: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    categorie: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    desc: {
        type: String,
        required: true,
        default: "",
    },
    filter: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});
const search = ref(props.filter.search);
watch(search, (value) => {
    setTimeout(() => {
        router.get(
            "/shop",
            { search: value },
            { preserveState: true, replace: true }
        );
    }, 600);
});
const page = usePage();
const local = page.props.locale;
const taux = ref();
const getDevise = async () => {
    try {
        await axios.get(route("devise.taux")).then((response) => {
            taux.value = response.data;
        });
    } catch (error) {
        console.error(error);
    }
};
getDevise();

const convertToPrice = (prixXOF) => {
    // Remplacez 655 par le taux de conversion de XOF à EUR
    const tauxConversion = taux.value;
    const prixEUR = prixXOF / tauxConversion;
    // Formatez le prix avec deux décimales
    if (local == "fr") {
        return new Intl.NumberFormat("fr-FR", {
            style: "currency",
            currency: "EUR",
        }).format(prixEUR.toFixed(2));
    } else if (local == "en") {
        return new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: "USD",
        }).format(prixEUR.toFixed(2));
    }
};
onMounted(() => {
    if ($(".sort-by-product-area").length) {
        var $body = $("body"),
            $cartWrap = $(".sort-by-product-area"),
            $cartContent = $cartWrap.find(".sort-by-dropdown");
        $cartWrap.on("click", ".sort-by-product-wrap", function (e) {
            e.preventDefault();
            var $this = $(this);
            if (!$this.parent().hasClass("show")) {
                $this
                    .siblings(".sort-by-dropdown")
                    .addClass("show")
                    .parent()
                    .addClass("show");
            } else {
                $this
                    .siblings(".sort-by-dropdown")
                    .removeClass("show")
                    .parent()
                    .removeClass("show");
            }
        });
        /*Close When Click Outside*/
        $body.on("click", function (e) {
            var $target = e.target;
            if (
                !$($target).is(".sort-by-product-area") &&
                !$($target).parents().is(".sort-by-product-area") &&
                $cartWrap.hasClass("show")
            ) {
                $cartWrap.removeClass("show");
                $cartContent.removeClass("show");
            }
        });
    }
});
</script>
<template>
    <Layout>
        <section class="mt-50 mb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">
                        <div class="shop-product-fillter style-2">
                            <div class="totall-product">
                                <h4 class="mb-3">{{ desc }}</h4>
                                <p>
                                    <strong class="text-brand">{{
                                        rows.total
                                    }}</strong>
                                    elements
                                </p>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="me-auto">
                                        <input
                                            v-model="search"
                                            type="text"
                                            placeholder="Recherche..."
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="product-list mb-50">
                            <div
                                class="product-cart-wrap"
                                v-for="item in rows.data"
                                :key="item"
                            >
                                <div class="product-img-action-wrap">
                                    <div class="product-img product-img-zoom">
                                        <div class="product-img-inner">
                                            <Link
                                                :href="
                                                    route('shop.show', item.id)
                                                "
                                            >
                                                <img
                                                    class="default-img"
                                                    v-bind:src="'/assets/imgs/shop/product-2-2.jpg'"
                                                    alt=""
                                                />
                                                <img
                                                    class="hover-img"
                                                    v-bind:src="'/assets/imgs/shop/product-3-2.jpg'"
                                                    alt=""
                                                />
                                            </Link>
                                        </div>
                                    </div>
                                    <div class="product-action-1">
                                        <Link
                                            :href="route('shop.show', item.id)"
                                            aria-label="Voir"
                                            class="action-btn hover-up"
                                        >
                                            <i class="fi-rs-eye"></i
                                        ></Link>
                                    </div>
                                    <div
                                        class="product-badges product-badges-position product-badges-mrg"
                                    >
                                        <span class="hot">Hot</span>
                                    </div>
                                </div>
                                <div class="product-content-wrap">
                                    <h2>
                                        <Link
                                            :href="route('shop.show', item.id)"
                                            >{{ item.nom }}</Link
                                        >
                                    </h2>
                                    <div class="product-price">
                                        <span>
                                            {{ convertToPrice(item.prix) }}
                                        </span>
                                        <span class="old-price">$245.8</span>
                                    </div>
                                    <p class="mt-15">
                                        Categorie: {{ item.categorie.nom }}
                                        <br />
                                        Taille: {{ item.taille }} <br />
                                    </p>

                                    <div class="product-action-1 show">
                                        <button
                                            type="button"
                                            aria-label="Acheté"
                                            class="action-btn"
                                            @click.prevent="
                                                AddToCard(
                                                    route('cart.store', item.id)
                                                )
                                            "
                                        >
                                            <i
                                                class="fi-rs-shopping-bag-add"
                                            ></i>
                                            Acheté
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--pagination-->
                        <Pagination :pagination="rows" />
                    </div>
                    <div class="col-lg-3 primary-sidebar sticky-sidebar">
                        <div class="widget-category mb-30">
                            <h5
                                class="section-title style-1 mb-30 wow fadeIn animated"
                            >
                                Categorie
                            </h5>
                            <ul class="categories">
                                <li v-for="item in categorie" :key="item">
                                    <Link :href="route('shop', item.id)">{{
                                        item.nom
                                    }}</Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </Layout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { Price_euro } from "@/helper";
import AddToCard from "@/helper";
const { event } = usePage();

const props = defineProps({
    items: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    news: {
        type: Boolean,
        default: false,
    },
    hot: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div
        class="col-lg-3 col-md-4 col-12 col-sm-6"
        v-for="item in items"
        :key="item"
    >
        <div class="product-cart-wrap mb-30">
            <div class="product-img-action-wrap">
                <div class="product-img product-img-zoom">
                    <Link :href="route('shop.show', item.id)">
                        <img
                            class="default-img"
                            src="/assets/imgs/shop/product-2-2.jpg"
                            alt=""
                        />
                        <img
                            class="hover-img"
                            src="/assets/imgs/shop/product-3-2.jpg"
                            alt=""
                        />
                    </Link>
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
                    v-if="news == true"
                    class="product-badges product-badges-position product-badges-mrg"
                >
                    <span class="new">Nouveauté</span>
                </div>
                <div
                    v-if="hot == true"
                    class="product-badges product-badges-position product-badges-mrg"
                >
                    <span class="hot">Hot</span>
                </div>
            </div>
            <div class="product-content-wrap">
                <div class="product-category">
                    <Link :href="route('shop.show', item.id)">
                        Categorie:
                        {{ item.categorie.nom }}
                    </Link>
                </div>
                <h2>
                    <Link :href="route('shop.show', item.id)">
                        {{ item.nom }}
                    </Link>
                </h2>
                <span>Taille {{ item.taille }}</span>
                <br />
                <span v-show="item.color">Couleur {{ item.color }}</span>
                <div class="product-price">
                    <span>
                        {{ Price_euro.format(item.prix) }}
                    </span>
                    <span class="old-price">$245.8</span>
                </div>
                <div class="product-action-1 show">
                    <button
                        type="button"
                        aria-label="Acheté"
                        class="action-btn"
                        @click.prevent="AddToCard(route('cart.store', item.id))"
                    >
                        <i class="fi-rs-shopping-bag-add"></i>
                        Acheté
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

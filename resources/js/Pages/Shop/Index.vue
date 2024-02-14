<script setup>
import Layout from "@/Shared/Layout.vue";
import Pagination from "@/Components/Pagination.vue";
import notify, { Price_format } from "@/notifications";
import { Link, router, useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";
const props = defineProps({
    rows: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    category: {
        type: Object,
        required: true,
        default: () => ({}),
    },
    // productId: {
    //     type: Number,
    //     required: true,
    //     default: () => ({}),
    // },
});

const AddToCard = (id) => {
    const form = useForm({
        id: id,
    });
    form.get(route("cart.store", form.id), {
        onSuccess: () => {
            form.reset();
            notify("Categorie ajouter avec success !", true);
        },
        onError: () => {
            notify(false);
        },
    });
    // axios
    //     .get(url)
    //     .then((response) => {
    // if (response.data.success) {
    //     router.reload();
    // }
    //     console.log(response);
    // })
    // .catch(function (error) {
    //     // handle error
    //     console.log(error);
    // });
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
                                <p>
                                    We found
                                    <strong class="text-brand">{{
                                        rows.total
                                    }}</strong>
                                    element for you!
                                </p>
                            </div>
                            <div class="sort-by-product-area">
                                <div class="sort-by-cover mr-10">
                                    <div class="sort-by-product-wrap">
                                        <div class="sort-by">
                                            <span
                                                ><i class="fi-rs-apps"></i
                                                >Show:</span
                                            >
                                        </div>
                                        <div class="sort-by-dropdown-wrap">
                                            <span>
                                                50
                                                <i
                                                    class="fi-rs-angle-small-down"
                                                ></i
                                            ></span>
                                        </div>
                                    </div>
                                    <div class="sort-by-dropdown">
                                        <ul>
                                            <li>
                                                <a class="active" href="#"
                                                    >50</a
                                                >
                                            </li>
                                            <li><a href="#">100</a></li>
                                            <li><a href="#">150</a></li>
                                            <li><a href="#">200</a></li>
                                            <li><a href="#">All</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="sort-by-cover">
                                    <div class="sort-by-product-wrap">
                                        <div class="sort-by">
                                            <span
                                                ><i class="fi-rs-apps-sort"></i
                                                >Sort by:</span
                                            >
                                        </div>
                                        <div class="sort-by-dropdown-wrap">
                                            <span>
                                                Featured
                                                <i
                                                    class="fi-rs-angle-small-down"
                                                ></i
                                            ></span>
                                        </div>
                                    </div>
                                    <div class="sort-by-dropdown">
                                        <ul>
                                            <li>
                                                <a class="active" href="#"
                                                    >Featured</a
                                                >
                                            </li>
                                            <li>
                                                <a href="#"
                                                    >Price: Low to High</a
                                                >
                                            </li>
                                            <li>
                                                <a href="#"
                                                    >Price: High to Low</a
                                                >
                                            </li>
                                            <li>
                                                <a href="#">Release Date</a>
                                            </li>
                                            <li><a href="#">Avg. Rating</a></li>
                                        </ul>
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
                                        <a
                                            aria-label="Acheté"
                                            class="action-btn hover-up"
                                            href=""
                                            ><i class="fi-rs-heart"></i
                                        ></a>
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
                                            {{ Price_format.format(item.prix) }}
                                        </span>
                                        <span class="old-price">$245.8</span>
                                    </div>
                                    <p class="mt-15">
                                        Categorie: {{ item.categorie.nom }}
                                        <br />
                                        Taille: {{ item.taille }} <br />
                                    </p>

                                    <div class="product-action-1 show">
                                        <Link
                                            type="button"
                                            aria-label="Buy now"
                                            class="action-btn"
                                            method="get"
                                            href=""
                                            @click.prevent="AddToCard(item.id)"
                                        >
                                            <i
                                                class="fi-rs-shopping-bag-add"
                                            ></i
                                            >Acheté
                                        </Link>
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
                                <li v-for="item in category" :key="item">
                                    <Link href="">{{ item.nom }}</Link>
                                </li>
                            </ul>
                        </div>
                        <!-- Fillter By Price -->
                        <div class="sidebar-widget price_range range mb-30">
                            <div
                                class="widget-header position-relative mb-20 pb-10"
                            >
                                <h5 class="widget-title mb-10">
                                    Fill by price
                                </h5>
                                <div class="bt-1 border-color-1"></div>
                            </div>
                            <div class="price-filter">
                                <div class="price-filter-inner">
                                    <div id="slider-range"></div>
                                    <div class="price_slider_amount">
                                        <div class="label-input">
                                            <span>Range:</span
                                            ><input
                                                type="text"
                                                id="amount"
                                                name="price"
                                                placeholder="Add Your Price"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group">
                                <div class="list-group-item mb-10 mt-10">
                                    <label class="fw-900">Color</label>
                                    <div class="custome-checkbox">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="checkbox"
                                            id="exampleCheckbox1"
                                            value=""
                                        />
                                        <label
                                            class="form-check-label"
                                            for="exampleCheckbox1"
                                            ><span>Red (56)</span></label
                                        >
                                        <br />
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="checkbox"
                                            id="exampleCheckbox2"
                                            value=""
                                        />
                                        <label
                                            class="form-check-label"
                                            for="exampleCheckbox2"
                                            ><span>Green (78)</span></label
                                        >
                                        <br />
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="checkbox"
                                            id="exampleCheckbox3"
                                            value=""
                                        />
                                        <label
                                            class="form-check-label"
                                            for="exampleCheckbox3"
                                            ><span>Blue (54)</span></label
                                        >
                                    </div>
                                    <label class="fw-900 mt-15"
                                        >Item Condition</label
                                    >
                                    <div class="custome-checkbox">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="checkbox"
                                            id="exampleCheckbox11"
                                            value=""
                                        />
                                        <label
                                            class="form-check-label"
                                            for="exampleCheckbox11"
                                            ><span>New (1506)</span></label
                                        >
                                        <br />
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="checkbox"
                                            id="exampleCheckbox21"
                                            value=""
                                        />
                                        <label
                                            class="form-check-label"
                                            for="exampleCheckbox21"
                                            ><span
                                                >Refurbished (27)</span
                                            ></label
                                        >
                                        <br />
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="checkbox"
                                            id="exampleCheckbox31"
                                            value=""
                                        />
                                        <label
                                            class="form-check-label"
                                            for="exampleCheckbox31"
                                            ><span>Used (45)</span></label
                                        >
                                    </div>
                                </div>
                            </div>
                            <a
                                href="shop-grid-right.html"
                                class="btn btn-sm btn-default"
                                ><i class="fi-rs-filter mr-5"></i> Fillter</a
                            >
                        </div>
                        <!-- Product sidebar Widget -->
                        <div
                            class="sidebar-widget product-sidebar mb-30 p-30 bg-grey border-radius-10"
                        >
                            <div
                                class="widget-header position-relative mb-20 pb-10"
                            >
                                <h5 class="widget-title mb-10">New products</h5>
                                <div class="bt-1 border-color-1"></div>
                            </div>
                            <div class="single-post clearfix">
                                <div class="image">
                                    <img
                                        src="assets/imgs/shop/thumbnail-3.jpg"
                                        alt="#"
                                    />
                                </div>
                                <div class="content pt-10">
                                    <h5>
                                        <a href="shop-product-detail.html"
                                            >Chen Cardigan</a
                                        >
                                    </h5>
                                    <p class="price mb-0 mt-5">$99.50</p>
                                    <div class="product-rate">
                                        <div
                                            class="product-rating"
                                            style="width: 90%"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <div class="single-post clearfix">
                                <div class="image">
                                    <img
                                        src="assets/imgs/shop/thumbnail-4.jpg"
                                        alt="#"
                                    />
                                </div>
                                <div class="content pt-10">
                                    <h6>
                                        <a href="shop-product-detail.html"
                                            >Chen Sweater</a
                                        >
                                    </h6>
                                    <p class="price mb-0 mt-5">$89.50</p>
                                    <div class="product-rate">
                                        <div
                                            class="product-rating"
                                            style="width: 80%"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <div class="single-post clearfix">
                                <div class="image">
                                    <img
                                        src="assets/imgs/shop/thumbnail-5.jpg"
                                        alt="#"
                                    />
                                </div>
                                <div class="content pt-10">
                                    <h6>
                                        <a href="shop-product-detail.html"
                                            >Colorful Jacket</a
                                        >
                                    </h6>
                                    <p class="price mb-0 mt-5">$25</p>
                                    <div class="product-rate">
                                        <div
                                            class="product-rating"
                                            style="width: 60%"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="banner-img wow fadeIn mb-45 animated d-lg-block d-none"
                        >
                            <img
                                src="assets/imgs/banner/banner-11.jpg"
                                alt=""
                            />
                            <div class="banner-text">
                                <span>Women Zone</span>
                                <h4>Save 17% on <br />Office Dress</h4>
                                <a href="shop-grid-right.html"
                                    >Shop Now <i class="fi-rs-arrow-right"></i
                                ></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </Layout>
</template>

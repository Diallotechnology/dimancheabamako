<x-app-layout>
    <style>
        .swiper {
            width: 100%;
            height: 100%;
        }

        .swiper-slide {
            text-align: center;
            font-size: 18px;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .swiper {
            width: 100%;
            height: 300px;
            margin-left: auto;
            margin-right: auto;
        }

        .swiper-slide {
            background-size: cover;
            background-position: center;
        }

        .mySwiper2 {
            height: 80%;
            width: 100%;
        }

        .mySwiper {
            height: 20%;
            box-sizing: border-box;
            padding: 10px 0;
        }

        .mySwiper .swiper-slide {
            width: 25%;
            height: 100%;
            opacity: 0.4;
        }

        .mySwiper .swiper-slide-thumb-active {
            opacity: 1;
        }

        .swiper-slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
    <section class="mt-50 mb-50">
        <div class="container">
            <div class="row">
                <div class="col-lg-9">
                    <div class="product-detail accordion-detail mb-4">
                        <div class="row mb-50">
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                {{-- <swiper :modules="[Thumbs]" :thumbs="{ swiper: thumbsSwiper }">
                                    <swiper-slide v-for="row in product.images" :key="row"><img v-bind:src="row.chemin"
                                            alt="product image" /></swiper-slide>
                                </swiper>

                                <swiper :modules="[Thumbs]" watch-slides-progress @swiper="setThumbsSwiper"
                                    :spaceBetween="10" :slidesPerView="4" :freeMode="true" :watchSlidesProgress="true">
                                    <swiper-slide v-for="row in product.images" :key="row"><img v-bind:src="row.chemin"
                                            alt="product image" /></swiper-slide>
                                </swiper> --}}
                                <div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff"
                                    class="swiper mySwiper2">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img src="https://swiperjs.com/demos/images/nature-1.jpg" />
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="https://swiperjs.com/demos/images/nature-2.jpg" />
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="https://swiperjs.com/demos/images/nature-3.jpg" />
                                        </div>
                                    </div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>
                                <div thumbsSlider="" class="swiper mySwiper">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img src="https://swiperjs.com/demos/images/nature-1.jpg" />
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="https://swiperjs.com/demos/images/nature-2.jpg" />
                                        </div>
                                        <div class="swiper-slide">
                                            <img src="https://swiperjs.com/demos/images/nature-3.jpg" />
                                        </div>

                                    </div>
                                </div>
                                <!-- End Gallery -->
                            </div>
                            <div class="col-md-6 col-sm-12 col-xs-12">
                                <div class="detail-info">
                                    <h2 class="title-detail">
                                        {{ $product->nom }}
                                    </h2>
                                    <div class="product-detail-rating">
                                        <div class="pro-details-brand">
                                            <span>
                                                Categorie:
                                                <a href="">
                                                    {{ $product->categorie->nom }}</a></span>
                                        </div>
                                    </div>
                                    <div class="clearfix product-price-cover">
                                        <div class="product-price primary-color float-left">
                                            <ins><span class="text-brand">
                                                    {{
                                                    $product->reduction >
                                                    0
                                                    ? $product->prix_promo
                                                    : $product->prix_format
                                                    }}
                                                </span>
                                            </ins>
                                            <ins>
                                                @if ($product->reduction > 0)
                                                <span class="old-price font-md ml-15">{{ $product->prix_format }}</span>
                                                @endif
                                            </ins>
                                            <span class="save-price font-md color3 ml-15 text-danger">{{
                                                $product->reduction > 0 ? "Bon plan -".$product->reduction."%": ""
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="bt-1 border-color-1 mt-15 mb-15"></div>
                                    <div class="short-desc mb-30">
                                        <p>
                                            {{ $product->resume }}
                                        </p>
                                    </div>
                                    <div class="product_sort_info font-xs mb-30">
                                        <ul>
                                            <li class="mb-10">
                                                <i class="fi-rs-crown mr-5"></i>Taille:
                                                {{ $product->taille }}
                                            </li>

                                            <li>
                                                En stock:
                                                {{
                                                $product->stock > 1
                                                ? "OUI"
                                                : "NON"
                                                }}
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="bt-1 border-color-1 mt-30 mb-30"></div>
                                    <div class="detail-extralink">
                                        <div class="product-extra-link2">
                                            <livewire:add-to-card :id="$product->id" />
                                        </div>
                                    </div>
                                </div>
                                <!-- Detail Info -->
                            </div>
                        </div>
                        <div class="tab-style3">
                            <ul class="nav nav-tabs text-uppercase">
                                <li class="nav-item">
                                    <a class="nav-link active" id="Description-tab" data-bs-toggle="tab"
                                        href="#Description">Description du produit</a>
                                </li>
                            </ul>
                            <div class="tab-content shop_info_tab entry-main-content">
                                <div class="tab-pane fade show active" id="Description">
                                    <div class="">
                                        <p>
                                            {{ $product->description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="row related-products mt-4">
                        <div class="row product-grid-4">
                            <livewire:cart-item :items="$rows" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-app-layout>
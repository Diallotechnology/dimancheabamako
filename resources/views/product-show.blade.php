<x-app-layout>
    <x-slot:metadata>
        <!-- Meta Description -->
        <meta name="description" content="{{ $product->nom }} : {{ $product->resume }}">

        <!-- Meta Keywords -->
        <meta name="keywords"
            content="{{ $product->nom }}, Bazin, Getzner, boubou, robes, prêt-à-porter, accessoires, Bamako">

        <!-- Google / Search Engine Tags -->
        <meta itemprop="name" content="{{ $product->nom }}">
        <meta itemprop="description" content="{{ $product->resume }}">
        <meta itemprop="image" content="{{ $product->cover }}">

        <!-- Facebook Meta Tags -->
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="product">
        <meta property="og:title" content="{{ $product->nom }}">
        <meta property="og:description" content="{{ $product->resume }}">
        <meta property="og:image" content="{{ $product->cover }}">

        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $product->nom }}">
        <meta name="twitter:description" content="{{ $product->resume }}">
        <meta name="twitter:image" content="{{ $product->cover }}">

        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- JSON-LD Structured Data -->
        <script type="application/ld+json">
            {
            "@context": "https://schema.org",
            "@type": "Product",
            "name": "{{ $product->nom }}",
            "image": "{{ $product->cover }}",
            "description": "{{ $product->resume }}",
            "brand": {
                "@type": "Brand",
                "name": "Dimanche à Bamako"
            },
            "offers": {
                "@type": "Offer",
                "priceCurrency": "XOF,EUR",
                "price": "{{ $product->prix }}",
                "availability": "https://schema.org/InStock",
                "url": "{{ url()->current() }}"
            }
        }
        </script>
    </x-slot:metadata>

    <x-slot:title>
        {{ $product->nom }}
        </x-slot>
        <section class="mt-50 mb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">
                        <div class="product-detail accordion-detail mb-4">
                            <div class="row mb-50">
                                <div class="col-md-6 col-sm-12 col-xs-12">
                                    <div style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff"
                                        class="swiper mySwiper2">
                                        <div class="swiper-wrapper">
                                            @foreach ($product->images as $row)
                                            <div class="swiper-slide">
                                                <img src="{{ $row->chemin }}" alt="{{ $row->nom }}" />
                                            </div>
                                            @endforeach
                                        </div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                    </div>
                                    <div thumbsSlider="" class="swiper mySwiper">
                                        <div class="swiper-wrapper">
                                            @foreach ($product->images as $row)
                                            <div class="swiper-slide">
                                                <img src="{{ $row->chemin }}" alt="{{ $row->nom }}" />
                                            </div>
                                            @endforeach
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
                                                <h5>Categorie: <span class="text-brand">{{ $product->categorie->nom
                                                        }}
                                                    </span>
                                                </h5>

                                                <h5>
                                                    Réference: <span class="text-brand">{{ $product->reference
                                                        }}</span>
                                                </h5>
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
                                                    <span class="old-price font-md ml-15">{{ $product->prix_format
                                                        }}</span>
                                                    @endif
                                                </ins>
                                                <span class="save-price font-md color3 ml-15 text-danger">{{
                                                    $product->reduction > 0 ? "Bon plan -".$product->reduction."%":
                                                    ""
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
                                                    <i class="fi-rs-crown mr-5"></i>@lang('messages.size'):
                                                    {{ $product->taille }}
                                                </li>

                                                <li>
                                                    @lang('messages.in_stock'):
                                                    {{
                                                    $product->stock >= 1
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
                                            href="#Description">@lang('messages.product_description')</a>
                                    </li>
                                </ul>
                                <div class="tab-content shop_info_tab entry-main-content">
                                    <div class="tab-pane fade show active" id="Description">
                                        <p>{{ $product->description }}</p>
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

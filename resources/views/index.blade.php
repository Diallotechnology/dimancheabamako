<x-app-layout>
    <x-slot:metadata>
        <!-- Meta Description -->
        <meta name="description" content="Dimanche à Bamako : {{ __('messages.bazin_sale') }}" />

        <!-- Meta Author -->
        <meta name="author" content="Dimanche à Bamako" />

        <!-- Meta Keywords -->
        <meta name="keywords"
            content="Bazin riche, Getzner Magnum, boubou, robes, prêt-à-porter, accessoires, mode africaine, Bamako, brodés, Wax, perlage, Siri, Taq" />

        <!-- Google / Search Engine Tags -->
        <meta itemprop="name" content="Dimanche à Bamako - E-commerce">
        <meta itemprop="description"
            content="Boutique en ligne de référence à Bamako pour Bazin riche teinté, Getzner Magnum, vêtements prêt-à-porter et plus encore.">
        <meta itemprop="image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">

        <!-- Facebook Meta Tags -->
        <meta property="og:url" content="https://www.dimancheabamako.com">
        <meta property="og:type" content="website">
        <meta property="og:title" content="Dimanche à Bamako - E-commerce pour Bazin, Getzner et prêt-à-porter">
        <meta property="og:description"
            content="Découvrez Dimanche à Bamako : une large sélection de Bazin, Getzner, robes, boubous et accessoires de mode africaine. Qualité et service premium.">
        <meta property="og:image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">

        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Dimanche à Bamako - Mode et Bazin en ligne">
        <meta name="twitter:description"
            content="Achetez en ligne votre Bazin riche teinté, Getzner, prêt-à-porter, accessoires, brodés et Wax. Livraison rapide et sécurisée.">
        <meta name="twitter:image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Store",
                "name": "Dimanche à Bamako",
                "description": "Boutique en ligne pour Bazin riche teinté, Getzner Magnum, boubous, robes prêt-à-porter, brodés, Wax et accessoires à Bamako.",
                "url": "https://www.dimancheabamako.com",
                "logo": "{{ asset('assets/imgs/theme/logo_meta_tag.png') }}",
                "image": "{{ asset('assets/imgs/theme/logo_meta_tag.png') }}",
                "address": {
                    "@type": "PostalAddress",
                    "streetAddress": "Rue de l'E-commerce, Bamako",
                    "addressLocality": "Bamako",
                    "addressCountry": "ML"
                },
                "telephone": "+223 20 00 00 00",
                "sameAs": [
                    "https://www.facebook.com/dimancheabamako",
                    "https://twitter.com/dimancheabamako",
                    "https://www.instagram.com/dimancheabamako"
                ],
                "openingHours": "Mo-Fr 09:00-18:00",
                "currenciesAccepted": "XOF,EUR",
                "paymentAccepted": ["Cash", "Credit Card", "Mobile Payment"],
                "priceRange": "$$"
            }
        </script>

        </x-slot>


        <section class="home-slider position-relative pt-50">
            <div class="hero-slider-1 dot-style-1 dot-style-1-position-1">
                @foreach ($slide as $item)
                <div class="single-hero-slider single-animation-wrap">
                    <div class="container">
                        <div class="row align-items-center slider-animated-1">
                            <div class="col-lg-5 col-md-6">
                                <div class="hero-slider-content-2">
                                    <h2 class="animated fw-900">
                                        {{ $item->text_one}}
                                    </h2>
                                    <h1 class="animated fw-900 text-brand">
                                        {{ $item->text_two }}
                                    </h1>
                                    <p class="animated">
                                        {{ $item->paragraph }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-6">
                                <div class="single-slider-img single-slider-img-1">
                                    <img class="animated slider-1-1" src="{{ $item->image }}" alt="slide image" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="slider-arrow hero-slider-1-arrow"></div>
        </section>
        <section class="product-tabs section-padding position-relative wow fadeIn animated">
            <div class="bg-square"></div>
            <div class="container">
                <div class="tab-header">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="nav-tab-two" data-bs-toggle="tab" data-bs-target="#tab-two"
                                type="button" role="tab" aria-controls="tab-two" aria-selected="false">
                                Populaire
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="active nav-link" id="nav-tab-three" data-bs-toggle="tab"
                                data-bs-target="#tab-three" type="button" role="tab" aria-controls="tab-three"
                                aria-selected="false">
                                @lang('messages.new')
                            </button>
                        </li>
                    </ul>
                    <a href="{{ route('category') }}" class="view-more d-none d-md-flex">
                        @lang('messages.view_all')
                        <i class="fi-rs-angle-double-small-right"></i>
                    </a>
                </div>
                <!--End nav-tabs-->
                <div class="tab-content wow fadeIn animated" id="myTabContent">
                    <div class="tab-pane fade" id="tab-two" role="tabpanel" aria-labelledby="tab-two">
                        <livewire:cart-item :items="$popular" :hot="true" />
                        <!--End product-grid-4-->
                    </div>
                    <!--En tab two (Popular)-->
                    <div class="tab-pane fade show active" id="tab-three" role="tabpanel" aria-labelledby="tab-three">
                        <livewire:cart-item :items="$latest" :news="true" />
                        <!--End product-grid-4-->
                    </div>
                    <!--En tab three (New added)-->
                </div>
                <!--End tab-content-->
            </div>
        </section>
</x-app-layout>
<x-app-layout>
    <section class="home-slider position-relative pt-50">
        <div class="hero-slider-1 dot-style-1 dot-style-1-position-1">
            @foreach ($slide as $item)
            <div class="single-hero-slider single-animation-wrap">
                <div class="container">
                    <div class="row align-items-center slider-animated-1">
                        <div class="col-lg-5 col-md-6">
                            <div class="hero-slider-content-2">
                                <h2 class="animated fw-900">
                                    {{-- {{ GoogleTranslate::trans($item->text_one, session('locale')) }} --}}
                                    {{ $item->text_one}}
                                </h2>
                                <h1 class="animated fw-900 text-brand">
                                    {{-- {{ GoogleTranslate::trans($item->text_two, session('locale')) }} --}}
                                    {{ $item->text_two }}
                                </h1>
                                <p class="animated">
                                    {{-- {{ GoogleTranslate::trans($item->paragraph, session('locale')) }} --}}
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
    <section class="featured section-padding position-relative">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                    <div class="banner-features wow fadeIn animated hover-up">
                        <img src="/assets/imgs/theme/icons/feature-1.png" alt="" />
                        <h4 class="bg-1">
                            {{-- {{ GoogleTranslate::trans('Livraison partout', session('locale')) }} --}}
                            Livraison partout
                        </h4>

                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                    <div class="banner-features wow fadeIn animated hover-up">
                        <img src="/assets/imgs/theme/icons/feature-2.png" alt="" />
                        <h4 class="bg-3">
                            {{-- {{ GoogleTranslate::trans("Commande en ligne", session('locale')) }} --}}
                            Commande en ligne
                        </h4>

                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                    <div class="banner-features wow fadeIn animated hover-up">
                        <img src="/assets/imgs/theme/icons/feature-3.png" alt="" />
                        <h4 class="bg-2">
                            {{-- {{ GoogleTranslate::trans("Économiser de l'argent", session('locale')) }} --}}
                            Économiser de l'argent
                        </h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                    <div class="banner-features wow fadeIn animated hover-up">
                        <img src="/assets/imgs/theme/icons/feature-4.png" alt="" />
                        <h4 class="bg-4">
                            {{-- {{ GoogleTranslate::trans("Promotions", session('locale')) }} --}}
                            Promotions
                        </h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                    <div class="banner-features wow fadeIn animated hover-up">
                        <img src="/assets/imgs/theme/icons/feature-5.png" alt="" />
                        <h4 class="bg-5">
                            {{-- {{ GoogleTranslate::trans("Bon shopping", session('locale')) }} --}}
                            Bon shopping
                        </h4>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-md-3 mb-lg-0">
                    <div class="banner-features wow fadeIn animated hover-up">
                        <img src="/assets/imgs/theme/icons/feature-6.png" alt="" />
                        <h4 class="bg-6">
                            {{-- {{ GoogleTranslate::trans("24/7 Support", session('locale')) }} --}}
                            24/7 Support
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="product-tabs section-padding position-relative wow fadeIn animated">
        <div class="bg-square"></div>
        <div class="container">
            <div class="tab-header">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    @empty(!$popular)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="nav-tab-two" data-bs-toggle="tab" data-bs-target="#tab-two"
                            type="button" role="tab" aria-controls="tab-two" aria-selected="false">
                            {{-- {{ GoogleTranslate::trans("Populaire", session('locale')) }} --}}
                            Populaire
                        </button>
                    </li>
                    @endempty
                    @empty(!$latest)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="nav-tab-three" data-bs-toggle="tab" data-bs-target="#tab-three"
                            type="button" role="tab" aria-controls="tab-three" aria-selected="false">
                            {{-- {{ GoogleTranslate::trans("Nouveauté", session('locale')) }} --}}
                            Nouveauté
                        </button>
                    </li>
                    @endempty
                </ul>
                <a href="{{ route('shop') }}" class="view-more d-none d-md-flex">
                    {{-- {{ GoogleTranslate::trans("Voir tout", session('locale')) }} --}}
                    Voir tout
                    <i class="fi-rs-angle-double-small-right"></i>
                </a>
            </div>
            <!--End nav-tabs-->
            <div class="tab-content wow fadeIn animated" id="myTabContent">
                <div class="tab-pane fade show active" id="tab-two" role="tabpanel" aria-labelledby="tab-two">
                    <livewire:cart-item :items="$popular" :hot="true" />
                    <!--End product-grid-4-->
                </div>
                <!--En tab two (Popular)-->
                <div class="tab-pane fade" id="tab-three" role="tabpanel" aria-labelledby="tab-three">
                    <livewire:cart-item :items="$latest" :news="true" />
                    <!--End product-grid-4-->
                </div>
                <!--En tab three (New added)-->
            </div>
            <!--End tab-content-->
        </div>
    </section>
</x-app-layout>
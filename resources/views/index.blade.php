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
                <a href="{{ route('shop') }}" class="view-more d-none d-md-flex">
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
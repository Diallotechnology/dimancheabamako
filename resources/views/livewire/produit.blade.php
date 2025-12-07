<div>
    <x-slot:metadata>
        <!-- Meta Description -->
        <meta name="description"
            content="Découvrez la collection {{ $cat->nom }} : une sélection de Bazin riche teinté, Getzner Magnum, boubous, robes Prêt-à-porter pour femmes et hommes, brodés et Wax, ordinateurs et perlage. Qualité premium garantie.">

        <!-- Meta Keywords -->
        <meta name="keywords"
            content="{{ $cat->nom }}, Bazin, Getzner, boubou, robes, prêt-à-porter, accessoires, Wax, brodés, ordinateurs, perlage, mode africaine">

        <!-- Google / Search Engine Tags -->
        <meta itemprop="name" content="Dimanche à Bamako - {{ $cat->nom }}">
        <meta itemprop="description"
            content="Explorez notre sélection {{ $cat->nom }} : Bazin riche teinté, Getzner Magnum, boubous et robes prêts-à-porter pour toutes occasions.">
        <meta itemprop="image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">

        <!-- Facebook Meta Tags -->
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="Dimanche à Bamako - {{ $cat->nom }}">
        <meta property="og:description"
            content="Découvrez la collection {{ $cat->nom }} : Bazin, Getzner, boubous, robes Prêt-à-porter et plus encore.">
        <meta property="og:image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">

        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Dimanche à Bamako - {{ $cat->nom }}">
        <meta name="twitter:description"
            content="Explorez notre sélection de {{ $cat->nom }} : vêtements, accessoires et produits d'exception.">
        <meta name="twitter:image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">

        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- JSON-LD Structured Data -->
        {{-- <script type="application/ld+json">
            {
            "@context": "https://schema.org",
            "@type": "ProductCollection",
            "name": "{{ $cat->nom }}",
            "description": "Découvrez une collection unique de {{ $cat->nom }} incluant Bazin riche teinté, Getzner Magnum, boubous, robes Prêt-à-porter et accessoires.",
            "url": "{{ url()->current() }}",
            "image": "{{ asset('assets/imgs/theme/logo_meta_tag.png') }}",
            "brand": {
                "@type": "Brand",
                "name": "Dimanche à Bamako"
            }
        } --}}
        </script>
    </x-slot:metadata>


    <x-slot:title>
        Categorie {{ $cat->nom }}
        </x-slot>
        <section class="mt-50 mb-50">
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">
                        <h3>{{ __('messages.category_text.title', ['name' => $cat->nom]) }}</h3>

                        <p>{{ __('messages.category_text.description_1', ['name' => $cat->nom]) }}</p>
                        <p>{{ __('messages.category_text.description_2') }}</p>
                        <div class="shop-product-fillter style-2">
                            <div class="totall-product">

                                <strong class="text-brand">{{
                                    $rows->total()
                                    }}</strong>
                                elements
                                </p>
                            </div>

                            <div class="mb-50">
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text" id="basic-addon-search31"><i
                                            class="fi-rs-search"></i></span>
                                    <input wire:model.live.debounce.200ms='search' type="text" class="form-control"
                                        placeholder="Recherche..." aria-label="Recherche..."
                                        aria-describedby="basic-addon-search31">
                                </div>
                            </div>
                        </div>
                        <div class="product-list mb-50">
                            <div wire:loading class="loader" wire:target="search"></div>
                            @forelse ($rows as $row)
                            <div class="product-cart-wrap" wire:target="search" wire:loading.class="d-none">
                                <div class="product-img-action-wrap">
                                    <div class="product-img product-img-zoom">
                                        <div class="product-img-inner">
                                            <a
                                                href="{{ route('shop.show', ['id' => $row->id, 'slug' => $row->slug]) }}">
                                                <img class="default-img" src="{{ $row->cover }}"
                                                    alt="{{ $row->nom }}" />
                                                <img class="hover-img" src="{{ $row->cover }}" alt="{{ $row->nom }}" />
                                            </a>
                                        </div>
                                    </div>
                                    <div class="product-action-1">
                                        <a href="{{ route('shop.show',  ['id' => $row->id, 'slug' => $row->slug]) }}"
                                            aria-label="Voir" class="action-btn hover-up">
                                            <i class="fi-rs-eye"></i></a>
                                    </div>
                                    <div class="product-badges product-badges-position product-badges-mrg">
                                        <span class="hot">{{
                                            $row->reduction > 0
                                            ? "Bon plan -"
                                            .$row->reduction.
                                            "%"
                                            : "hot"
                                            }}</span>
                                    </div>
                                </div>
                                <div class="product-content-wrap">
                                    <h2>
                                        <a href="{{ route('shop.show',  ['id' => $row->id, 'slug' => $row->slug]) }}">{{
                                            $row->nom }}</a>
                                    </h2>
                                    <div class="product-price">
                                        <span>
                                            {{
                                            $row->reduction > 0
                                            ? $row->prix_promo
                                            : $row->prix_format
                                            }}
                                        </span>
                                        @if ($row->reduction > 0)
                                        <span class="old-price">
                                            {{ $row->prix_format }}
                                        </span>
                                        @endif
                                    </div>
                                    <p class="mt-15">
                                        Categorie: {{ $row->categorie->nom }}
                                        <br />
                                        @lang('messages.size') {{ $row->taille }} <br />
                                        @lang('messages.color') {{ $row->color }}
                                    </p>
                                    @if ($row['is_preorder'])
                                    <span class="ml-1 text-danger">
                                        @lang('messages.product_status.commande')
                                    </span>
                                    @else
                                    <span class="ml-1 text-success">
                                        @lang('messages.product_status.disponible')
                                    </span>
                                    @endif
                                    <div class="product-action-1 show">
                                        <button type="button" aria-label="@lang('messages.purchased')"
                                            class="action-btn" wire:click='add({{ $row->id }})'>
                                            <i class="fi-rs-shopping-bag-add"></i>
                                            @lang('messages.purchased')
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <h4 class="text-center my-5">@lang('messages.no_product_available')</h4>
                            @endforelse
                        </div>
                        <!--pagination-->
                        @if($rows)
                        {{ $rows->links() }}
                        @endif
                    </div>
                    <div class="col-lg-3 primary-sidebar sticky-sidebar">
                        <div class="widget-category mb-30">
                            <h5 class="section-title style-1 mb-30 wow fadeIn animated">
                                Categories
                            </h5>
                            <ul class="categories">
                                @foreach ($categories as $row)
                                <li>
                                    <a
                                        href="{{ route('shop', ['category'=>$row->id,'slug'=>Str::slug($row->nom, '-')]) }}">
                                        {{ $row->nom }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
</div>
@script
<script>
    $wire.on('productAdded', () => {
        var modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
        modal.show();
    });
</script>
@endscript
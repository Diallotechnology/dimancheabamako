<div>
    <section class="mt-50 mb-50">
        <div class="container">
            <div class="row">
                <div class="col-lg-9">
                    <div class="shop-product-fillter style-2">
                        <div class="totall-product">
                            <h4 class="mb-3"></h4>
                            <p>
                                <strong class="text-brand">{{
                                    $rows->total()
                                    }}</strong>
                                elements
                            </p>
                        </div>
                        <div class="sidebar-widget widget_search mb-50">
                            <div class="search-form">
                                <div>
                                    <input wire:model.live.debounce.200ms="search" type="text"
                                        placeholder="Recherche..." class="form-control" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="product-list mb-50">
                        @forelse ($rows as $row)
                        <div class="product-cart-wrap">
                            <div class="product-img-action-wrap">
                                <div class="product-img product-img-zoom">
                                    <div class="product-img-inner">
                                        <a href="{{ route('shop.show', $row->id) }}">
                                            <img class="default-img" src="{{ $row->cover }}" alt="produit image" />
                                            <img class="hover-img" src="{{ $row->cover }}" alt="produit image hover" />
                                        </a>
                                    </div>
                                </div>
                                <div class="product-action-1">
                                    <a href="{{ route('shop.show', $row->id) }}" aria-label="Voir"
                                        class="action-btn hover-up">
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
                                    <a href="{{ route('shop.show', $row->id) }}">{{ $row->nom }}</a>
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
                                    Taille: {{ $row->taille }} <br />
                                </p>

                                <div class="product-action-1 show">
                                    <button type="button" aria-label="Acheté" class="action-btn"
                                        wire:click='add({{ $row->id }})'>
                                        <i class="fi-rs-shopping-bag-add"></i>
                                        {{-- {{ GoogleTranslate::trans("", session('locale')) }} --}}
                                        Acheté
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <h4 class="text-center my-5">
                            {{-- {{ GoogleTranslate::trans("Aucun produit disponible", session('locale')) }} --}}
                        </h4>
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
                            @foreach ($category_list as $row)
                            <li>
                                <a href="{{ route('shop', $row->id) }}">
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
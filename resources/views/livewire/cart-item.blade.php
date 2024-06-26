<div>
    <div class="row product-grid-4">
        @foreach ($items as $item)
        <div class="col-lg-3 col-md-4 col-12 col-sm-6" wire:key='{{ $item->id }}'>
            <div class="product-cart-wrap mb-30">
                <div class="product-img-action-wrap">
                    <div class="product-img product-img-zoom">
                        <a href="{{ route('shop.show', $item->id) }}">
                            <img class="default-img" src="{{ $item->cover }}" alt="produit image" />
                            <img class="hover-img" src="{{ $item->cover }}" alt="produit image hover" />
                        </a>
                    </div>
                    <div class="product-action-1">
                        <a href="{{ route('shop.show', $item->id) }}" aria-label="Voir" class="action-btn hover-up">
                            <i class="fi-rs-eye"></i></a>
                    </div>
                    @if ($news == true)
                    <div class="product-badges product-badges-position product-badges-mrg">
                        <span class="new">Nouveauté</span>
                    </div>
                    @endif
                    @if ($hot == true)
                    <div class="product-badges product-badges-position product-badges-mrg">
                        <span class="hot">{{
                            $item->reduction > 0
                            ? "Bon plan -".$item->reduction."%"
                            : "hot"
                            }}</span>
                    </div>
                    @endif
                </div>
                <div class="product-content-wrap">
                    <div class="product-category">
                        <a href="{{ route('shop.show', $item->id) }}">
                            Categorie:
                            {{ $item->categorie->nom }}
                        </a>
                    </div>
                    <h2>
                        <a href="{{ route('shop.show', $item->id) }}">
                            {{ $item->nom }}
                        </a>
                    </h2>
                    <span>Taille {{ $item->taille }}</span>
                    <br />
                    <span>Couleur {{ $item->color }}</span>
                    <div class="product-price">
                        <span>
                            {{
                            $item->reduction > 0
                            ? $item->prix_promo
                            : $item->prix_format
                            }}
                        </span>
                        @if ($item->reduction > 0)
                        <span class="old-price">
                            {{ $item->prix_format }}
                        </span>
                        @endif
                    </div>
                    <div class="product-action-1 show">
                        <button type="button" aria-label="Acheté" class="action-btn" wire:click='add({{ $item->id }})'>
                            <i class="fi-rs-shopping-bag-add"></i>
                            Acheté
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @endforeach
    </div>
</div>
@script
<script>
    $wire.on('productAdded', () => {
        var modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
        modal.show();
    });
</script>
@endscript
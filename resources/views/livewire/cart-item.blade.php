<div>

    <div class="row product-grid-4">
        @forelse ($items as $item)
        <div class="col-lg-3 col-md-4 col-12 col-sm-6" wire:key="product-{{ $item['id'] }}">
            <div class="product-cart-wrap mb-30">
                <div class="product-img-action-wrap">
                    <div class="product-img product-img-zoom">
                        <a href="{{ route('shop.show', ['id' => $item['id'], 'slug' => $item['slug']]) }}">
                            <img class="default-img" src="{{ $item['cover'] }}" alt="{{ $item['nom'] }}" />
                            <img class="hover-img" src="{{ $item['cover'] }}" alt="{{ $item['nom'] }}" />
                        </a>
                    </div>

                    <div class="product-action-1">
                        <a href="{{ route('shop.show', ['id' => $item['id'], 'slug' => $item['slug']]) }}"
                            aria-label="@lang('messages.view')" class="action-btn hover-up">
                            <i class="fi-rs-eye"></i>
                        </a>
                    </div>

                    @if ($news)
                    <div class="product-badges product-badges-position product-badges-mrg">
                        <span class="new">@lang('messages.new')</span>
                    </div>
                    @endif

                    @if ($hot)
                    <div class="product-badges product-badges-position product-badges-mrg">
                        <span class="hot">
                            {{ $item['reduction'] > 0 ? 'Bon plan -'.$item['reduction'].'%' : 'hot' }}
                        </span>
                    </div>
                    @endif

                </div>

                <div class="product-content-wrap">
                    <div class="product-category">
                        Ref: {{ $item['reference'] }}
                    </div>

                    <div class="product-category">
                        <a href="{{ route('shop.show', ['id' => $item['id'], 'slug' => $item['slug']]) }}">
                            Categorie: {{ $item['categorie'] }}
                        </a>
                    </div>

                    <h2>
                        <a href="{{ route('shop.show', ['id' => $item['id'], 'slug' => $item['slug']]) }}">
                            {{ $item['nom'] }}
                        </a>
                    </h2>

                    <span>@lang('messages.size') {{ $item['taille'] }}</span><br />
                    <span>@lang('messages.color') {{ $item['color'] }}</span>

                    <div class="product-price d-flex justify-content-between">
                        @if ($item['reduction'] > 0)
                        <span>
                            {{ $item['prix_promo'] }}
                            {{ $item['is_preorder'] ? '/'. __('messages.product_status.unit') : '' }}
                        </span>
                        @else
                        <span>
                            {{ $item['prix_format'] }}
                            {{ $item['is_preorder'] ? '/'. __('messages.product_status.unit') : '' }}
                        </span>
                        @endif


                        @if ($item['is_preorder'])
                        <span class="ml-1 text-danger">
                            @lang('messages.product_status.commande')
                        </span>
                        @else
                        <span class="ml-1 text-success">
                            @lang('messages.product_status.disponible')
                        </span>
                        @endif

                        @if ($item['reduction'] > 0)
                        <span class="old-price">
                            {{ $item['prix_format'] }} {{ $item['is_preorder'] ??
                            __('messages.product_status.unit') }}
                        </span>
                        @endif

                    </div>

                    <div class="product-action-1 show">
                        <button type="button" aria-label="Acheté" class="action-btn"
                            wire:click="add({{ $item['id'] }})">
                            <i class="fi-rs-shopping-bag-add"></i>
                            @lang('messages.purchased')
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <h3 class="text-center">Aucun produit</h3>
        @endforelse
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
{{--
    Carte produit unique de la vitrine. `$item` = tableau plat de App\Helper\ProductView::fromModel().
    L'ajout au panier passe par l'événement Livewire `add-to-cart`, reçu par QuickAddModal (une seule instance par page).
    <x-shop.product-card :item="$item" badge="new" />   (badge : null ou 'new')
--}}
@props(['item', 'badge' => null])

@php
    $url = route('shop.show', ['id' => $item['id'], 'slug' => $item['slug']]);
    $discount = (int) ($item['reduction'] ?? 0);
@endphp

<article class="group flex h-full flex-col">
    <a href="{{ $url }}" class="relative block overflow-hidden rounded-2xl bg-sage-soft" tabindex="-1" aria-hidden="true">
        <img src="{{ $item['cover'] }}" alt="" loading="lazy" decoding="async" width="480" height="600"
            class="aspect-[4/5] w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">

        @if ($discount > 0)
            <span class="absolute top-3 left-3 rounded-full bg-brand px-2.5 py-1 text-xs font-bold text-ink shadow-card">
                {{ __('messages.shop.sale', ['percent' => $discount]) }}
            </span>
        @elseif ($badge === 'new')
            <span class="absolute top-3 left-3 rounded-full bg-ink px-2.5 py-1 text-xs font-bold text-white shadow-card">
                {{ __('messages.new') }}
            </span>
        @endif
    </a>

    <div class="mt-3 flex flex-1 flex-col">
        @if (! empty($item['categorie']))
            <p class="text-xs font-semibold tracking-wide text-brand-strong uppercase">{{ $item['categorie'] }}</p>
        @endif

        <h3 class="mt-0.5 text-base leading-snug">
            <a href="{{ $url }}" class="hover:text-brand-strong focus-visible:outline-offset-4">{{ $item['nom'] }}</a>
        </h3>

        <p class="mt-1 text-sm text-ink-muted">
            @if (! empty($item['taille']))
                {{ __('messages.size') }} {{ $item['taille'] }}
            @endif
            @if (! empty($item['taille']) && ! empty($item['color']))
                <span aria-hidden="true">·</span>
            @endif
            @if (! empty($item['color']))
                {{ $item['color'] }}
            @endif
        </p>

        <div class="mt-2 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
            <x-shop.price :amount="$item['prix_final']" :compare-at="$discount > 0 ? $item['prix'] : null"
                :suffix="$item['is_preorder'] ? '/ '.__('messages.product_status.unit') : null" />

            <span @class([
                'inline-flex items-center gap-1.5 text-xs font-medium',
                'text-danger' => $item['is_preorder'],
                'text-success' => ! $item['is_preorder'],
            ])>
                <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                {{ $item['is_preorder'] ? __('messages.product_status.commande') : __('messages.product_status.disponible') }}
            </span>
        </div>

        <div class="mt-auto pt-4">
            <button type="button" @click="Livewire.dispatch('add-to-cart', { id: {{ (int) $item['id'] }} })"
                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-full border border-ink/20 text-sm font-semibold transition-colors hover:border-brand hover:bg-brand active:scale-[0.98]">
                <x-shop.icon name="bag" class="size-4" />
                {{ __('messages.purchased') }}
                <span class="sr-only">— {{ $item['nom'] }}</span>
            </button>
        </div>
    </div>
</article>

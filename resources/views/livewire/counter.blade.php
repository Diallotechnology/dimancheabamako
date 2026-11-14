{{-- Racine unique obligatoire : un @if au sommet fait perdre à Livewire son élément racine. `contents` = wrapper invisible. --}}
<div class="contents">
    @if ($variant === 'shop')
    <a href="{{ route('panier') }}" class="relative inline-flex rounded-md p-2 text-ink transition-colors hover:text-brand-strong">
        <x-shop.icon name="bag" class="size-6" />
        <span class="sr-only">{{ __('messages.shop.cart_count', ['count' => $count]) }}</span>
        @if ($count > 0)
        <span aria-hidden="true"
            class="absolute top-0.5 right-0.5 grid min-w-5 place-items-center rounded-full bg-brand px-1 text-[11px] leading-5 font-bold text-ink">
            {{ $count }}
        </span>
        @endif
    </a>
    @else
    <a class="mini-cart-icon" href="{{ route('panier') }}">
        <img alt="cart" src="{{ asset('assets/imgs/theme/icons/icon-cart.svg') }}" />
        <span class="pro-count blue">
            {{ $count }}
        </span>
    </a>
    @endif
</div>

{{--
    Prix affiché dans la devise de la session, via PriceService::format (montants en CFA).
    <x-shop.price :amount="$prixFinal" :compare-at="$prixAvantRemise" suffix="/ unité" />
--}}
@props(['amount', 'compareAt' => null, 'suffix' => null])

@php
    $hasDiscount = $compareAt !== null && $compareAt > $amount;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-baseline gap-x-2']) }}>
    <span class="font-display font-bold text-ink">
        {{ \App\Service\PriceService::format($amount) }}@if ($suffix)<span class="ml-1 text-sm font-normal text-ink-muted">{{ $suffix }}</span>@endif
    </span>
    @if ($hasDiscount)
        <del class="text-sm text-ink-muted">
            <span class="sr-only">{{ __('messages.shop.price_before_discount') }} :</span>
            {{ \App\Service\PriceService::format($compareAt) }}
        </del>
    @endif
</span>

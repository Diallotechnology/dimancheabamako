{{--
    En-tête de page de la vitrine : fil d'Ariane (Accueil / titre) et h1, sur fond lin.
    <x-shop.page-header :title="__('messages.about')" />   Slot : texte d'introduction (facultatif).
--}}
@props(['title'])

<div class="border-b border-sage bg-linen">
    <div class="container-shop py-8 md:py-10">
        <nav aria-label="{{ __('messages.shop.breadcrumb') }}" class="mb-3 text-sm text-ink-muted">
            <ol class="flex flex-wrap items-center gap-x-2">
                <li><a href="{{ route('home') }}" class="hover:text-ink">{{ __('messages.home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-semibold text-ink">{{ $title }}</li>
            </ol>
        </nav>
        <h1 class="text-2xl md:text-4xl">{{ $title }}</h1>
        @if (! $slot->isEmpty())
            <div class="mt-3 max-w-3xl text-ink-muted">{{ $slot }}</div>
        @endif
    </div>
</div>

{{--
    Layout de la vitrine refondue (Tailwind via Vite, sans Bootstrap ni jQuery).
    - Pages Blade : <x-shop-layout> (App\View\Components\ShopLayout).
    - Composants Livewire pleine page : #[Layout('layouts::shop')].
    Slots : `title` (texte du <title>), `metadata` (<x-shop.seo …>).
    Coexistence avec l'ancien layout Evara (layouts/app.blade.php) : n'inclure ici AUCUNE partielle de celui-ci.
--}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>{{ isset($title) && trim((string) $title) !== '' ? trim((string) $title).' · Dimanche à Bamako' : __('messages.home').' '.__('messages.sunday_sale') }}</title>
    {{ $metadata ?? '' }}
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    @fonts(['lato', 'league-spartan'])
    @vite(['resources/css/shop.css', 'resources/js/shop.ts'])

    @production
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-KKFP18GDQ9"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-KKFP18GDQ9');
    </script>
    @endproduction
</head>

<body class="flex min-h-dvh flex-col">
    <a href="#contenu"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:rounded-full focus:bg-ink focus:px-4 focus:py-2 focus:text-white">
        {{ __('messages.shop.skip_to_content') }}
    </a>

    @if ($downMessage = session()->pull('down_message'))
    <div role="status" class="bg-brand-soft px-4 py-2 text-center text-sm font-semibold text-ink">
        {{ $downMessage }}
    </div>
    @endif

    <x-shop.header />

    <main id="contenu" class="flex-1">
        {{ $slot }}
    </main>

    <x-shop.footer />

    {{-- Modale « produit ajouté » : une instance par page, écoute l'événement `add-to-cart` des cartes produit. --}}
    <livewire:quick-add-modal />

    @stack('scripts')
</body>

</html>

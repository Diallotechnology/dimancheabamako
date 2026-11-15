{{--
    Page d'erreur autonome (Laravel la sert pour 4xx/5xx via 4xx.blade.php et 5xx.blade.php).
    Volontairement sans layout shop : pas de requête base de données, pas de Livewire ni de session lue,
    pour rester affichable même quand l'erreur vient de la base (500/503). Seul le CSS Vite est chargé.
--}}
@php
    $status = isset($exception) && method_exists($exception, 'getStatusCode') ? (int) $exception->getStatusCode() : 500;
    $key = \Illuminate\Support\Facades\Lang::has("messages.error_pages.$status") ? (string) $status : 'default';
    $title = __("messages.error_pages.$key.title");
    $text = $key === '403' && isset($exception) && $exception->getMessage() !== ''
        ? $exception->getMessage()
        : __("messages.error_pages.$key.text");
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $status }} · {{ $title }} · Dimanche à Bamako</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    @fonts(['lato', 'league-spartan'])
    @vite('resources/css/shop.css')
</head>

<body class="grid min-h-dvh place-items-center bg-linen px-4">
    <main class="w-full max-w-lg py-12 text-center">
        <a href="{{ route('home') }}" class="inline-block" aria-label="Dimanche à Bamako">
            <img src="{{ asset('images/logo.svg') }}" alt="" width="72" height="72" class="mx-auto h-16 w-auto">
        </a>
        <p class="mt-8 font-display text-7xl font-semibold tracking-tight text-brand-strong" aria-hidden="true">{{ $status }}</p>
        <h1 class="mt-2 text-2xl sm:text-3xl">{{ $title }}</h1>
        <p class="mx-auto mt-3 max-w-md text-ink-muted">{{ $text }}</p>
        @if ($status !== 503)
        <x-shop.button :href="route('home')" class="mt-8">{{ __('messages.back_to_home') }}</x-shop.button>
        @endif
    </main>
</body>

</html>

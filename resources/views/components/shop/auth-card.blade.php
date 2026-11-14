{{--
    Carte centrée des pages d'authentification (connexion, inscription, mot de passe…).
    <x-shop.auth-card :title="__('messages.login')" :intro="…" width="max-w-2xl"> … </x-shop.auth-card>
--}}
@props(['title', 'intro' => null, 'width' => 'max-w-md'])

<div class="container-shop py-10 md:py-16">
    <div class="mx-auto w-full {{ $width }} rounded-2xl border border-sage bg-white p-6 shadow-card sm:p-8">
        <h1 class="text-2xl">{{ $title }}</h1>
        @if ($intro)
            <p class="mt-2 text-sm text-ink-muted">{{ $intro }}</p>
        @endif
        <div class="mt-6">{{ $slot }}</div>
    </div>
</div>

{{--
    En-tête de la vitrine (layout `shop`). Un seul balisage pour mobile et desktop :
    la navigation passe dans un tiroir sous `lg`. `$categories` vient du view composer global.
--}}
@php
    $links = [
        ['route' => 'home', 'label' => __('messages.home')],
        ['route' => 'about', 'label' => __('messages.about')],
        ['route' => 'livraison', 'label' => __('messages.delivery')],
        ['route' => 'contact', 'label' => __('messages.contact')],
    ];
    $isEuro = session('devise') === 'EUR';
    $isEnglish = app()->getLocale() === 'en';
    $navLink = 'rounded-md px-3 py-2 text-[15px] font-medium transition-colors hover:text-brand-strong';
    $menuItem = 'flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-ink transition-colors hover:bg-sage-soft';
@endphp

{{-- Wrapper `contents` : partage l'état Alpine entre l'en-tête et le tiroir sans casser le `sticky` de l'en-tête. --}}
<div x-data="{ drawer: false }" @keydown.escape.window="drawer = false" class="contents">
<header class="sticky top-0 z-40 border-b border-sage bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/85">
    {{-- Bandeau utilitaire : contact, devise, langue (desktop) --}}
    <div class="hidden border-b border-sage-soft bg-linen lg:block">
        <div class="container-shop flex h-10 items-center justify-between text-sm text-ink-muted">
            <a href="tel:+22366035154" class="inline-flex items-center gap-2 hover:text-ink">
                <x-shop.icon name="phone" class="size-4" />
                <span>{{ __('messages.shop.call_us') }} : +223 66 03 51 54</span>
            </a>
            <div class="flex items-center gap-2">
                <x-shop.dropdown align="right" width="w-36">
                    <x-slot:trigger>
                        <x-shop.icon name="coins" class="size-4" />
                        <span class="sr-only">{{ __('messages.shop.currency') }} :</span>
                        {{ $isEuro ? 'Euro (€)' : 'CFA' }}
                    </x-slot:trigger>
                    <a role="menuitem" class="{{ $menuItem }}" href="{{ route('change_devise', 'CFA') }}">CFA</a>
                    <a role="menuitem" class="{{ $menuItem }}" href="{{ route('change_devise', 'EUR') }}">Euro (€)</a>
                </x-shop.dropdown>
                <x-shop.dropdown align="right" width="w-36">
                    <x-slot:trigger>
                        <x-shop.icon name="globe" class="size-4" />
                        <span class="sr-only">{{ __('messages.shop.language') }} :</span>
                        {{ $isEnglish ? 'English' : 'Français' }}
                    </x-slot:trigger>
                    <a role="menuitem" class="{{ $menuItem }}" href="{{ route('change_language', 'fr') }}" hreflang="fr" lang="fr">Français</a>
                    <a role="menuitem" class="{{ $menuItem }}" href="{{ route('change_language', 'en') }}" hreflang="en" lang="en">English</a>
                </x-shop.dropdown>
            </div>
        </div>
    </div>

    {{-- Barre principale --}}
    <div class="container-shop flex h-16 items-center gap-4 lg:h-20">
        <button type="button" @click="drawer = true" class="-ml-2 rounded-md p-2 text-ink lg:hidden"
            :aria-expanded="drawer.toString()" aria-controls="shop-drawer">
            <x-shop.icon name="menu" class="size-6" />
            <span class="sr-only">{{ __('messages.shop.open_menu') }}</span>
        </button>

        <a href="{{ route('home') }}" class="shrink-0">
            <img src="{{ asset('images/logo.svg') }}" alt="Dimanche à Bamako" class="h-9 w-auto lg:h-11"
                width="160" height="44">
        </a>

        <nav aria-label="{{ __('messages.shop.main_menu') }}" class="ml-6 hidden flex-1 items-center gap-1 lg:flex">
            <a href="{{ route('home') }}" @class([$navLink, 'text-brand-strong' => request()->routeIs('home')])
                @if (request()->routeIs('home')) aria-current="page" @endif>{{ __('messages.home') }}</a>

            <x-shop.dropdown width="w-60">
                <x-slot:trigger>{{ __('messages.category') }}</x-slot:trigger>
                @foreach ($categories as $category)
                    <a role="menuitem" class="{{ $menuItem }}"
                        href="{{ route('shop', ['category' => $category->id, 'slug' => Str::slug($category->nom, '-')]) }}">
                        {{ $category->nom }}
                    </a>
                @endforeach
            </x-shop.dropdown>

            @foreach (array_slice($links, 1) as $link)
                <a href="{{ route($link['route']) }}" @class([$navLink, 'text-brand-strong' => request()->routeIs($link['route'])])
                    @if (request()->routeIs($link['route'])) aria-current="page" @endif>{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-1">
            @auth
                <x-shop.dropdown align="right" width="w-52" class="hidden lg:block">
                    <x-slot:trigger>
                        <x-shop.icon name="user" />
                        <span class="sr-only">{{ __('messages.shop.account') }}</span>
                    </x-slot:trigger>
                    <a role="menuitem" class="{{ $menuItem }}" href="{{ route('profil') }}">
                        <x-shop.icon name="user" class="size-4" /> {{ __('messages.shop.profile') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" role="menuitem" class="{{ $menuItem }} text-danger">
                            <x-shop.icon name="log-out" class="size-4" /> {{ __('messages.logout') }}
                        </button>
                    </form>
                </x-shop.dropdown>
            @else
                <a href="{{ route('login') }}"
                    class="hidden items-center gap-2 rounded-md px-3 py-2 text-sm font-medium hover:text-brand-strong lg:inline-flex">
                    <x-shop.icon name="user" /> {{ __('messages.login') }}
                </a>
            @endauth

            @livewire('counter')
        </div>
    </div>

</header>

{{-- Tiroir mobile : hors du <header> (son backdrop-filter piégerait ce bloc `fixed` dans la hauteur de l'en-tête). --}}
    <div x-cloak x-show="drawer" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true"
        aria-label="{{ __('messages.shop.main_menu') }}" id="shop-drawer">
        <div x-show="drawer" x-transition.opacity class="absolute inset-0 bg-ink/40" @click="drawer = false"></div>
        <div x-show="drawer" x-trap.inert.noscroll="drawer"
            x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="-translate-x-full"
            x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="-translate-x-full"
            class="absolute inset-y-0 left-0 flex w-[min(20rem,85vw)] flex-col overflow-y-auto bg-white shadow-pop">
            <div class="flex h-16 items-center justify-between border-b border-sage px-4">
                <img src="{{ asset('images/logo-sm.svg') }}" alt="Dimanche à Bamako" class="h-8 w-auto">
                <button type="button" @click="drawer = false" class="rounded-md p-2">
                    <x-shop.icon name="x" class="size-6" />
                    <span class="sr-only">{{ __('messages.shop.close_menu') }}</span>
                </button>
            </div>

            <nav class="flex flex-col p-2 text-base">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}" @class([
                        'rounded-lg px-3 py-3 font-medium hover:bg-sage-soft',
                        'text-brand-strong' => request()->routeIs($link['route']),
                    ])>{{ $link['label'] }}</a>
                @endforeach

                <div x-data="{ open: false }">
                    <button type="button" @click="open = ! open" :aria-expanded="open.toString()"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-3 font-medium hover:bg-sage-soft">
                        {{ __('messages.category') }}
                        <x-shop.icon name="chevron-down" class="size-4 transition-transform" ::class="open && 'rotate-180'" />
                    </button>
                    <div x-show="open" x-collapse class="ml-3 border-l border-sage pl-2">
                        @foreach ($categories as $category)
                            <a class="block rounded-lg px-3 py-2.5 text-[15px] text-ink-muted hover:bg-sage-soft hover:text-ink"
                                href="{{ route('shop', ['category' => $category->id, 'slug' => Str::slug($category->nom, '-')]) }}">
                                {{ $category->nom }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </nav>

            <div class="mt-auto space-y-4 border-t border-sage p-4 text-sm">
                <div>
                    <p class="mb-2 font-semibold">{{ __('messages.shop.currency') }}</p>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('change_devise', 'CFA') }}" @class(['rounded-lg border px-3 py-2 text-center', 'border-brand bg-brand-soft font-semibold' => ! $isEuro, 'border-sage' => $isEuro])>CFA</a>
                        <a href="{{ route('change_devise', 'EUR') }}" @class(['rounded-lg border px-3 py-2 text-center', 'border-brand bg-brand-soft font-semibold' => $isEuro, 'border-sage' => ! $isEuro])>Euro (€)</a>
                    </div>
                </div>
                <div>
                    <p class="mb-2 font-semibold">{{ __('messages.shop.language') }}</p>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('change_language', 'fr') }}" lang="fr" @class(['rounded-lg border px-3 py-2 text-center', 'border-brand bg-brand-soft font-semibold' => ! $isEnglish, 'border-sage' => $isEnglish])>Français</a>
                        <a href="{{ route('change_language', 'en') }}" lang="en" @class(['rounded-lg border px-3 py-2 text-center', 'border-brand bg-brand-soft font-semibold' => $isEnglish, 'border-sage' => ! $isEnglish])>English</a>
                    </div>
                </div>
                @auth
                    <div class="flex items-center justify-between">
                        <a href="{{ route('profil') }}" class="inline-flex items-center gap-2 font-medium">
                            <x-shop.icon name="user" class="size-4" /> {{ __('messages.shop.profile') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 text-danger">
                                <x-shop.icon name="log-out" class="size-4" /> {{ __('messages.logout') }}
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}"
                        class="flex items-center justify-center gap-2 rounded-lg bg-ink px-4 py-2.5 font-semibold text-white">
                        <x-shop.icon name="user" class="size-4" /> {{ __('messages.login') }}
                    </a>
                @endauth
                <a href="tel:+22366035154" class="flex items-center gap-2 text-ink-muted">
                    <x-shop.icon name="phone" class="size-4" /> +223 66 03 51 54
                </a>
            </div>
        </div>
    </div>
</div>

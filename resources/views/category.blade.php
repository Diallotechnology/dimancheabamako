<x-shop-layout>
    <x-slot:title>{{ __('messages.shop.categories') }}</x-slot:title>

    <x-slot:metadata>
        <x-shop.seo
            title="Dimanche à Bamako - {{ __('messages.shop.categories') }}"
            description="Parcourez toutes les catégories de Dimanche à Bamako : Bazin, Getzner, boubous, robes et accessoires de mode africaine."
            :canonical="route('categorie_mobile')" />
    </x-slot:metadata>

    <div class="border-b border-sage bg-linen">
        <div class="container-shop py-8 md:py-10">
            <nav aria-label="{{ __('messages.shop.breadcrumb') }}" class="mb-3 text-sm text-ink-muted">
                <ol class="flex flex-wrap items-center gap-x-2">
                    <li><a href="{{ route('home') }}" class="hover:text-ink">{{ __('messages.home') }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="font-semibold text-ink">{{ __('messages.shop.categories') }}</li>
                </ol>
            </nav>
            <h1 class="text-2xl md:text-4xl">{{ __('messages.shop.categories') }}</h1>
        </div>
    </div>

    <section class="container-shop py-8 md:py-12">
        @if ($categories->isNotEmpty())
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
            @foreach ($categories as $row)
            <li>
                <a href="{{ route('shop', ['category' => $row->id, 'slug' => Str::slug($row->nom)]) }}"
                    class="group flex min-h-28 items-end justify-between gap-2 rounded-2xl border border-sage bg-sage-soft p-4 transition-colors hover:border-ink hover:bg-white">
                    <span class="font-display text-lg font-semibold">{{ $row->nom }}</span>
                    <x-shop.icon name="chevron-right" class="size-5 text-ink-muted transition-transform group-hover:translate-x-1" />
                </a>
            </li>
            @endforeach
        </ul>
        @else
        <p class="py-12 text-center text-ink-muted">{{ __('messages.no_product_available') }}</p>
        @endif
    </section>
</x-shop-layout>

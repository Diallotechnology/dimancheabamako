<x-shop-layout>
    <x-slot:metadata>
        <x-shop.seo
            title="Dimanche à Bamako - E-commerce pour Bazin, Getzner et prêt-à-porter"
            description="Dimanche à Bamako : une large sélection de Bazin, Getzner, robes, boubous et accessoires de mode africaine. Qualité et service premium."
            keywords="Bazin riche, Getzner Magnum, boubou, robes, prêt-à-porter, accessoires, mode africaine, Bamako, brodés, Wax, perlage, Siri, Taq"
            :canonical="route('home')" />
    </x-slot:metadata>

    <h1 class="sr-only">Dimanche à Bamako — {{ __('messages.sunday_sale') }}</h1>

    {{-- Diaporama : un seul bloc Alpine, sans bibliothèque. Pause au survol/focus, désactivé si « réduire les animations ». --}}
    @if ($slide->isNotEmpty())
    <section class="border-b border-sage bg-linen" aria-roledescription="carousel" aria-label="{{ __('messages.shop.slides') }}"
        x-data="{
            i: 0,
            n: {{ $slide->count() }},
            paused: false,
            timer: null,
            go(k) { this.i = (k + this.n) % this.n },
            init() {
                if (this.n < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                this.timer = setInterval(() => { if (! this.paused) this.go(this.i + 1) }, 6500);
            },
            destroy() { clearInterval(this.timer) },
        }"
        @mouseenter="paused = true" @mouseleave="paused = false" @focusin="paused = true" @focusout="paused = false">
        <div class="container-shop">
            <div class="grid">
                @foreach ($slide as $item)
                <div class="col-start-1 row-start-1 grid items-center gap-6 py-8 transition-opacity duration-700 md:grid-cols-2 md:gap-10 md:py-14"
                    role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} / {{ $loop->count }}"
                    :class="i === {{ $loop->index }} ? 'opacity-100' : 'pointer-events-none opacity-0'"
                    x-bind:inert="i !== {{ $loop->index }}" @if (! $loop->first) inert @endif>
                    <div class="order-2 md:order-1">
                        <p class="font-display text-2xl font-semibold text-ink md:text-4xl">{{ $item->text_one }}</p>
                        <h2 class="mt-1 text-3xl font-bold text-brand-strong md:text-5xl">{{ $item->text_two }}</h2>
                        <p class="mt-4 max-w-md text-base text-ink-muted md:text-lg">{{ $item->paragraph }}</p>
                        <x-shop.button :href="route('categorie_mobile')" class="mt-6">{{ __('messages.shop.shop_now') }}</x-shop.button>
                    </div>
                    <div class="order-1 md:order-2">
                        <img src="{{ $item->image }}" alt="" width="720" height="540"
                            class="aspect-[4/3] w-full rounded-3xl object-cover shadow-card"
                            @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                    </div>
                </div>
                @endforeach
            </div>

            @if ($slide->count() > 1)
            <div class="flex items-center justify-center gap-3 pb-6">
                <button type="button" @click="go(i - 1)" class="rounded-full border border-sage bg-white p-2 hover:border-ink">
                    <x-shop.icon name="chevron-right" class="size-4 rotate-180" />
                    <span class="sr-only">{{ __('messages.shop.previous') }}</span>
                </button>
                <div class="flex gap-2">
                    @foreach ($slide as $item)
                    <button type="button" @click="go({{ $loop->index }})" :aria-current="i === {{ $loop->index }}"
                        class="h-2.5 rounded-full transition-all" :class="i === {{ $loop->index }} ? 'w-8 bg-brand' : 'w-2.5 bg-ink/20 hover:bg-ink/40'">
                        <span class="sr-only">{{ __('messages.shop.go_to_slide', ['n' => $loop->iteration]) }}</span>
                    </button>
                    @endforeach
                </div>
                <button type="button" @click="go(i + 1)" class="rounded-full border border-sage bg-white p-2 hover:border-ink">
                    <x-shop.icon name="chevron-right" class="size-4" />
                    <span class="sr-only">{{ __('messages.shop.next') }}</span>
                </button>
            </div>
            @endif
        </div>
    </section>
    @endif

    {{-- Produits : Nouveautés / Populaires --}}
    <section class="container-shop py-12 md:py-16" x-data="{ tab: 'latest' }">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4 border-b border-sage">
            <div role="tablist" aria-label="{{ __('messages.view_all') }}" class="-mb-px flex gap-6">
                @foreach (['latest' => __('messages.shop.latest'), 'popular' => __('messages.shop.popular')] as $key => $label)
                <button type="button" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}"
                    @click="tab = '{{ $key }}'" :aria-selected="(tab === '{{ $key }}').toString()"
                    class="border-b-2 pb-3 font-display text-lg font-semibold transition-colors"
                    :class="tab === '{{ $key }}' ? 'border-brand text-ink' : 'border-transparent text-ink-muted hover:text-ink'">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <a href="{{ route('categorie_mobile') }}"
                class="mb-3 inline-flex items-center gap-1 text-sm font-semibold hover:text-brand-strong">
                {{ __('messages.view_all') }}
                <x-shop.icon name="chevron-right" class="size-4" />
            </a>
        </div>

        @foreach (['latest' => [$latest, 'new'], 'popular' => [$popular, null]] as $key => [$items, $badge])
        <div role="tabpanel" id="panel-{{ $key }}" aria-labelledby="tab-{{ $key }}" x-show="tab === '{{ $key }}'" @if ($key !== 'latest') x-cloak @endif>
            @if (count($items) > 0)
            <ul class="grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">
                @foreach ($items as $item)
                <li><x-shop.product-card :item="$item" :badge="$badge" /></li>
                @endforeach
            </ul>
            @else
            <p class="py-12 text-center text-ink-muted">{{ __('messages.shop.no_products') }}</p>
            @endif
        </div>
        @endforeach
    </section>
</x-shop-layout>

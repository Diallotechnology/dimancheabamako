<div>
    <x-slot:title>{{ $cat->nom }}</x-slot:title>

    <x-slot:metadata>
        <x-shop.seo
            :title="'Dimanche à Bamako - '.$cat->nom"
            :description="'Découvrez la collection '.$cat->nom.' : Bazin, Getzner, boubous, robes prêt-à-porter et plus encore. Qualité premium garantie.'"
            :keywords="$cat->nom.', Bazin, Getzner, boubou, robes, prêt-à-porter, accessoires, Wax, brodés, perlage, mode africaine'"
            :canonical="route('shop', ['category' => $cat->id, 'slug' => Str::slug($cat->nom)])" />
    </x-slot:metadata>

    <div class="border-b border-sage bg-linen">
        <div class="container-shop py-8 md:py-10">
            <nav aria-label="{{ __('messages.shop.breadcrumb') }}" class="mb-3 text-sm text-ink-muted">
                <ol class="flex flex-wrap items-center gap-x-2">
                    <li><a href="{{ route('home') }}" class="hover:text-ink">{{ __('messages.home') }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('categorie_mobile') }}" class="hover:text-ink">{{ __('messages.shop.categories') }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="font-semibold text-ink">{{ $cat->nom }}</li>
                </ol>
            </nav>
            <h1 class="text-2xl md:text-4xl">{{ __('messages.category_text.title', ['name' => $cat->nom]) }}</h1>
            <p class="mt-3 max-w-3xl text-ink-muted">
                {{ __('messages.category_text.description_1', ['name' => $cat->nom]) }}
                {{ __('messages.category_text.description_2') }}
            </p>
        </div>
    </div>

    <section id="catalogue" class="container-shop py-8 md:py-12" aria-labelledby="catalogue-titre">
        <h2 id="catalogue-titre" class="sr-only">{{ __('messages.shop.categories') }}</h2>

        {{-- Catégories : défilement horizontal sur mobile, liens classiques (URL partageables, SEO). --}}
        <nav aria-label="{{ __('messages.shop.categories') }}" class="-mx-4 mb-6 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0">
            <ul class="flex w-max gap-2 sm:w-auto sm:flex-wrap">
                @foreach ($categories as $category)
                    @php($active = $category->id === $cat->id)
                    <li>
                        <a href="{{ route('shop', ['category' => $category->id, 'slug' => Str::slug($category->nom)]) }}"
                            @if ($active) aria-current="page" @endif
                            @class([
                                'inline-flex h-10 items-center rounded-full border px-4 text-sm font-semibold whitespace-nowrap transition-colors',
                                'border-ink bg-ink text-white' => $active,
                                'border-sage bg-white hover:border-ink' => ! $active,
                            ])>
                            {{ $category->nom }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="mb-8 flex flex-wrap items-center justify-between gap-x-6 gap-y-3">
            <p class="text-sm text-ink-muted" role="status" aria-live="polite">
                {{ trans_choice('messages.shop.results_count', $this->products->total()) }}
            </p>

            <div class="relative w-full sm:w-80">
                <label for="recherche" class="sr-only">{{ __('messages.shop.search_products') }}</label>
                <x-shop.icon name="search" class="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-ink-muted" />
                <input id="recherche" type="search" wire:model.live.debounce.250ms="search" autocomplete="off"
                    placeholder="{{ __('messages.shop.search_placeholder') }}"
                    class="h-11 w-full rounded-full border border-sage bg-white pr-4 pl-11 text-sm placeholder:text-ink-muted focus:border-ink">
            </div>
        </div>

        {{-- Squelettes pendant la recherche / le changement de page. --}}
        <ul wire:loading.grid wire:target="search, gotoPage, nextPage, previousPage" aria-hidden="true"
            class="grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">
            @foreach (range(1, 8) as $n)
                <li class="animate-pulse">
                    <div class="aspect-[4/5] rounded-2xl bg-sage-soft"></div>
                    <div class="mt-3 h-3 w-1/3 rounded bg-sage-soft"></div>
                    <div class="mt-2 h-4 w-3/4 rounded bg-sage-soft"></div>
                    <div class="mt-3 h-11 rounded-full bg-sage-soft"></div>
                </li>
            @endforeach
        </ul>

        <div wire:loading.remove wire:target="search, gotoPage, nextPage, previousPage">
            @if ($this->products->isNotEmpty())
                <ul class="grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:grid-cols-4 lg:gap-x-6">
                    @foreach ($this->products as $item)
                        <li wire:key="p-{{ $item['id'] }}"><x-shop.product-card :item="$item" /></li>
                    @endforeach
                </ul>

                <div class="mt-12">{{ $this->products->links() }}</div>
            @else
                <div class="py-16 text-center">
                    <p class="text-ink-muted">
                        {{ $search !== '' ? __('messages.shop.no_results') : __('messages.no_product_available') }}
                    </p>
                    @if ($search !== '')
                        <button type="button" wire:click="$set('search', '')"
                            class="mt-4 inline-flex h-11 items-center rounded-full border border-ink/20 px-6 text-sm font-semibold hover:border-brand hover:bg-brand">
                            {{ __('messages.shop.reset_search') }}
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </section>
</div>

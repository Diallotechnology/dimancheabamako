@php
    $url = route('shop.show', ['id' => $product->id, 'slug' => $product->slug]);
    $discount = (int) $product->reduction;
    $available = $product->is_preorder || $product->stock >= 1;

    // Galerie : vidéo d'abord (optionnelle), puis les images ; à défaut, la couverture.
    $slides = collect();
    if ($product->video) {
        $slides->push(['type' => 'video', 'src' => $product->VideoLink()]);
    }
    foreach ($product->images as $image) {
        $slides->push(['type' => 'image', 'src' => $image->chemin]);
    }
    if ($slides->where('type', 'image')->isEmpty()) {
        $slides->push(['type' => 'image', 'src' => $product->cover]);
    }
    $description = $product->resume ?: __('messages.shop.default_description');
@endphp

<x-shop-layout>
    <x-slot:title>{{ $product->nom }}</x-slot:title>

    <x-slot:metadata>
        <x-shop.seo :title="$product->nom" :description="$product->nom.' : '.$description" :image="$product->cover"
            type="product" :canonical="$url"
            :keywords="$product->nom.', Bazin, Getzner, boubou, robes, prêt-à-porter, accessoires, Bamako'" />
    </x-slot:metadata>

    <div class="container-shop py-6 md:py-10">
        <nav aria-label="{{ __('messages.shop.breadcrumb') }}" class="mb-6 text-sm text-ink-muted">
            <ol class="flex flex-wrap items-center gap-x-2">
                <li><a href="{{ route('home') }}" class="hover:text-ink">{{ __('messages.home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li>
                    <a href="{{ route('shop', ['category' => $product->categorie_id, 'slug' => Str::slug($product->categorie->nom)]) }}"
                        class="hover:text-ink">{{ $product->categorie->nom }}</a>
                </li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-semibold text-ink">{{ $product->nom }}</li>
            </ol>
        </nav>

        <div class="grid gap-8 md:grid-cols-2 lg:gap-14">
            {{-- Galerie : image/vidéo principale + vignettes, un seul bloc Alpine. --}}
            <div x-data="{ i: 0, n: {{ $slides->count() }}, go(k) { this.i = (k + this.n) % this.n } }"
                @keydown.left="go(i - 1)" @keydown.right="go(i + 1)" role="group"
                aria-roledescription="carousel" aria-label="{{ __('messages.shop.gallery') }}">
                <div class="relative overflow-hidden rounded-2xl bg-sage-soft">
                    @foreach ($slides as $slide)
                        <div x-show="i === {{ $loop->index }}" @if (! $loop->first) x-cloak @endif>
                            @if ($slide['type'] === 'video')
                                <video controls preload="metadata" class="aspect-[4/5] w-full bg-ink object-contain">
                                    <source src="{{ $slide['src'] }}" type="video/mp4">
                                </video>
                            @else
                                <img src="{{ $slide['src'] }}" alt="{{ $product->nom }}" width="800" height="1000"
                                    @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                    class="aspect-[4/5] w-full object-cover">
                            @endif
                        </div>
                    @endforeach

                    @if ($discount > 0)
                        <span class="absolute top-4 left-4 rounded-full bg-brand px-3 py-1 text-sm font-bold text-ink shadow-card">
                            {{ __('messages.shop.sale', ['percent' => $discount]) }}
                        </span>
                    @endif

                    @if ($slides->count() > 1)
                        <button type="button" @click="go(i - 1)"
                            class="absolute top-1/2 left-3 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow-card hover:bg-white">
                            <x-shop.icon name="chevron-right" class="size-5 rotate-180" />
                            <span class="sr-only">{{ __('messages.shop.previous') }}</span>
                        </button>
                        <button type="button" @click="go(i + 1)"
                            class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow-card hover:bg-white">
                            <x-shop.icon name="chevron-right" class="size-5" />
                            <span class="sr-only">{{ __('messages.shop.next') }}</span>
                        </button>
                    @endif
                </div>

                @if ($slides->count() > 1)
                    <ul class="mt-3 flex gap-2 overflow-x-auto pb-1">
                        @foreach ($slides as $slide)
                            <li class="shrink-0">
                                <button type="button" @click="i = {{ $loop->index }}"
                                    :aria-current="(i === {{ $loop->index }}).toString()"
                                    class="block size-16 overflow-hidden rounded-xl border-2 bg-sage-soft transition-colors sm:size-20"
                                    :class="i === {{ $loop->index }} ? 'border-ink' : 'border-transparent hover:border-sage'">
                                    <span class="sr-only">{{ $slide['type'] === 'video' ? __('messages.shop.show_video') : __('messages.shop.show_image', ['n' => $loop->iteration]) }}</span>
                                    @if ($slide['type'] === 'video')
                                        <span class="flex size-full items-center justify-center bg-ink text-xs font-bold text-white" aria-hidden="true">▶</span>
                                    @else
                                        <img src="{{ $slide['src'] }}" alt="" loading="lazy" class="size-full object-cover" aria-hidden="true">
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold tracking-wide text-brand-strong uppercase">{{ $product->categorie->nom }}</p>
                <h1 class="mt-1 text-3xl md:text-4xl">{{ $product->nom }}</h1>
                <p class="mt-1 text-sm text-ink-muted">{{ __('messages.shop.reference', ['ref' => $product->reference]) }}</p>

                <div class="mt-5 text-2xl">
                    <x-shop.price :amount="$product->prix_final_base" :compare-at="$discount > 0 ? (int) $product->prix : null"
                        :suffix="$product->is_preorder ? '/ '.__('messages.product_status.unit') : null" />
                </div>

                @if ($product->resume)
                    <p class="mt-5 text-ink-muted">{{ $product->resume }}</p>
                @endif

                <dl class="mt-6 divide-y divide-sage border-y border-sage text-sm">
                    @if ($product->taille)
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-ink-muted">{{ __('messages.size') }}</dt>
                            <dd class="font-semibold">{{ $product->taille }}</dd>
                        </div>
                    @endif
                    @if ($product->color)
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-ink-muted">{{ __('messages.color') }}</dt>
                            <dd class="font-semibold">{{ $product->color }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-ink-muted">{{ __('messages.shop.availability') }}</dt>
                        <dd @class([
                            'inline-flex items-center gap-1.5 font-semibold',
                            'text-danger' => $product->is_preorder || ! $available,
                            'text-success' => ! $product->is_preorder && $available,
                        ])>
                            <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>
                            @if (! $available)
                                {{ __('messages.shop.unavailable') }}
                            @else
                                {{ $product->is_preorder ? __('messages.product_status.commande') : __('messages.product_status.disponible') }}
                            @endif
                        </dd>
                    </div>
                </dl>

                <div class="mt-8">
                    @if ($available)
                        <button type="button" @click="Livewire.dispatch('add-to-cart', { id: {{ $product->id }} })"
                            class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-brand px-8 font-semibold text-ink transition-colors hover:bg-ink hover:text-white active:scale-[0.98] sm:w-auto">
                            <x-shop.icon name="bag" class="size-5" />
                            {{ __('messages.purchased') }}
                        </button>
                    @else
                        <button type="button" disabled
                            class="inline-flex h-12 w-full cursor-not-allowed items-center justify-center rounded-full bg-sage px-8 font-semibold text-ink-muted sm:w-auto">
                            {{ __('messages.shop.unavailable') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <section class="mt-14 border-t border-sage pt-8" aria-labelledby="description-titre">
            <h2 id="description-titre" class="text-xl">{{ __('messages.product_description') }}</h2>
            <p class="mt-4 max-w-3xl whitespace-pre-line text-ink-muted">{{ $product->description ?: __('messages.shop.default_description') }}</p>
        </section>

        @if (count($rows) > 0)
            <section class="mt-14 border-t border-sage pt-8" aria-labelledby="related-titre">
                <h2 id="related-titre" class="mb-6 text-xl">{{ __('messages.shop.related') }}</h2>
                <ul class="grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4 lg:gap-x-6">
                    @foreach ($rows as $item)
                        <li><x-shop.product-card :item="$item" /></li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-shop-layout>

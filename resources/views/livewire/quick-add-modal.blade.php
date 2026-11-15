{{-- Modale « produit ajouté » de la vitrine (variante `shop` de QuickAddModal). Ouverte par window.showQuick() (CartAction::store). --}}
<div x-data="{ open: false }" x-on:quick-modal-open.window="open = true" x-on:keydown.escape.window="open = false">
    <div x-cloak x-show="open" class="fixed inset-0 z-[70] grid place-items-center p-4" role="dialog" aria-modal="true"
        aria-labelledby="quick-add-title">
        <div x-show="open" x-transition.opacity class="absolute inset-0 bg-ink/50" @click="open = false"></div>

        <div x-show="open" x-trap.noscroll="open" x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="translate-y-4 scale-95 opacity-0" x-transition:leave="transition duration-100 ease-in"
            x-transition:leave-end="scale-95 opacity-0"
            class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-pop">
            <button type="button" @click="open = false"
                class="absolute top-3 right-3 rounded-full p-2 text-ink-muted hover:bg-sage-soft hover:text-ink">
                <x-shop.icon name="x" />
                <span class="sr-only">{{ __('messages.shop.close_menu') }}</span>
            </button>

            @if ($addedItem !== [])
                <p class="mb-4 text-sm font-semibold text-success">{{ __('messages.shop.added_to_cart') }}</p>

                <div class="flex items-center gap-4">
                    <img src="{{ $addedItem['attributes']['cover'] ?? '' }}" alt="" class="size-20 shrink-0 rounded-xl bg-sage-soft object-cover">
                    <div class="min-w-0">
                        <h2 id="quick-add-title" class="truncate text-lg">{{ $addedItem['name'] ?? '' }}</h2>
                        <x-shop.price :amount="$pricing['unit']" :compare-at="$pricing['original']" class="mt-1" />
                        @if ($pricing['reduction'] > 0)
                            <span class="mt-1 inline-block rounded-full bg-brand px-2 py-0.5 text-xs font-bold text-ink">-{{ $pricing['reduction'] }}%</span>
                        @endif
                        @if (! empty($addedItem['poids']))
                            <p class="mt-1 text-sm text-ink-muted">{{ __('messages.shop.weight', ['kg' => $addedItem['poids']]) }}</p>
                        @endif
                    </div>
                </div>

                @if (session()->has('warning'))
                    <p role="alert" class="mt-4 rounded-lg bg-danger/10 px-3 py-2 text-center text-sm font-medium text-danger">
                        {{ session('warning') }}
                    </p>
                @endif

                @if ($addedItem['is_preorder'] ?? false)
                    <fieldset class="mt-5">
                        <legend class="mb-2 text-sm font-semibold">{{ __('messages.shop.choose_length') }}</legend>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ([5, 6] as $length)
                                <button type="button" wire:click="setQuickQuantity({{ $length }})"
                                    aria-pressed="{{ ($addedItem['quantity'] ?? 0) === $length ? 'true' : 'false' }}"
                                    @class([
                                        'rounded-xl border px-3 py-2.5 text-center font-semibold transition-colors',
                                        'border-brand bg-brand-soft' => ($addedItem['quantity'] ?? 0) === $length,
                                        'border-sage hover:border-ink/40' => ($addedItem['quantity'] ?? 0) !== $length,
                                    ])>
                                    {{ $length }} {{ __('messages.product_status.unit') }}
                                </button>
                            @endforeach
                        </div>
                    </fieldset>
                @endif

                <p class="mt-5 flex items-baseline justify-between border-t border-sage pt-4">
                    <span class="text-ink-muted">Total</span>
                    <span class="font-display text-xl font-bold">{{ \App\Service\PriceService::format($pricing['total']) }}</span>
                </p>

                <div class="mt-5 grid gap-2">
                    <x-shop.button wire:click="goToCart" wire:loading.attr="disabled" wire:target="goToCart" size="lg">
                        {{ __('messages.finalize_order') }}
                    </x-shop.button>
                    <x-shop.button variant="outline" @click="open = false">
                        {{ __('messages.continue_shopping') }}
                    </x-shop.button>
                </div>
            @endif
        </div>
    </div>
</div>

@script
<script>
    window.showQuick = () => window.dispatchEvent(new CustomEvent('quick-modal-open'));
</script>
@endscript

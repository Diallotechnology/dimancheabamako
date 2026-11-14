{{--
    Pagination Livewire de la vitrine (Tailwind, thème `shop` de config/livewire.php).
    Navigation par boutons Livewire ; le défilement remonte au début du catalogue.
--}}
@php
    $pageName = $paginator->getPageName();
    $scroll = "(\$el.closest('#catalogue') || document.body).scrollIntoView()";
    $item = 'inline-flex size-11 items-center justify-center rounded-full border text-sm font-semibold transition-colors';
@endphp

@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('messages.shop.pagination') }}" class="flex flex-col items-center gap-3">
    <ul class="flex flex-wrap items-center justify-center gap-2">
        <li>
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="{{ $item }} border-sage text-ink-muted/50">
                    <x-shop.icon name="chevron-right" class="size-4 rotate-180" />
                    <span class="sr-only">{{ __('pagination.previous') }}</span>
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $pageName }}')" x-on:click="{{ $scroll }}"
                    class="{{ $item }} border-sage hover:border-ink">
                    <x-shop.icon name="chevron-right" class="size-4 rotate-180" />
                    <span class="sr-only">{{ __('pagination.previous') }}</span>
                </button>
            @endif
        </li>

        {{-- Petits écrans : « Page x sur y » ; à partir de sm : numéros. --}}
        <li class="px-3 text-sm text-ink-muted sm:hidden" aria-live="polite">
            {{ __('messages.shop.page_of', ['page' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}
        </li>

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="hidden sm:block" aria-hidden="true"><span class="px-1 text-ink-muted">{{ $element }}</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <li class="hidden sm:block" wire:key="page-{{ $pageName }}-{{ $page }}">
                        @if ($page === $paginator->currentPage())
                            <span aria-current="page" class="{{ $item }} border-ink bg-ink text-white">
                                <span class="sr-only">{{ __('messages.shop.page_n', ['n' => $page]) }}</span>
                                <span aria-hidden="true">{{ $page }}</span>
                            </span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" x-on:click="{{ $scroll }}"
                                aria-label="{{ __('messages.shop.page_n', ['n' => $page]) }}"
                                class="{{ $item }} border-sage hover:border-ink">{{ $page }}</button>
                        @endif
                    </li>
                @endforeach
            @endif
        @endforeach

        <li>
            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $pageName }}')" x-on:click="{{ $scroll }}"
                    class="{{ $item }} border-sage hover:border-ink">
                    <x-shop.icon name="chevron-right" class="size-4" />
                    <span class="sr-only">{{ __('pagination.next') }}</span>
                </button>
            @else
                <span aria-disabled="true" class="{{ $item }} border-sage text-ink-muted/50">
                    <x-shop.icon name="chevron-right" class="size-4" />
                    <span class="sr-only">{{ __('pagination.next') }}</span>
                </span>
            @endif
        </li>
    </ul>
</nav>
@endif

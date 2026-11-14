{{--
    Menu déroulant accessible (clavier, Échap, clic extérieur) en Alpine.
    <x-shop.dropdown align="right">
        <x-slot:trigger>Libellé</x-slot:trigger>
        <a href="…" role="menuitem">…</a>
    </x-shop.dropdown>
--}}
@props(['align' => 'left', 'width' => 'w-48'])

<div x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false"
    {{ $attributes->merge(['class' => 'relative']) }}>
    <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="true"
        class="inline-flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm font-medium text-ink transition-colors hover:text-brand-strong">
        {{ $trigger }}
        <x-shop.icon name="chevron-down" class="size-4 transition-transform duration-200" ::class="open && 'rotate-180'" />
    </button>

    <div x-cloak x-show="open" x-transition.origin.top role="menu"
        @class([
            'absolute z-50 mt-2 overflow-hidden rounded-xl border border-sage bg-white py-1.5 shadow-pop',
            $width,
            'right-0' => $align === 'right',
            'left-0' => $align !== 'right',
        ])>
        {{ $slot }}
    </div>
</div>

{{--
    Bouton de la vitrine. Rend un <a> si `href` est fourni, sinon un <button>.
    Variantes : primary (or, texte encre), dark (encre), outline, ghost. Tailles : sm, md, lg.
--}}
@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'type' => 'button'])

@php
    $classes = \Illuminate\Support\Arr::toCssClasses([
        'inline-flex items-center justify-center gap-2 rounded-full font-semibold transition-[background-color,border-color,color,transform] duration-150 active:scale-[0.98] disabled:pointer-events-none disabled:opacity-50',
        'bg-brand text-ink hover:bg-[#c48f00]' => $variant === 'primary',
        'bg-ink text-white hover:bg-black' => $variant === 'dark',
        'border border-ink/20 bg-white text-ink hover:border-ink' => $variant === 'outline',
        'text-ink hover:bg-sage-soft' => $variant === 'ghost',
        'h-9 px-4 text-sm' => $size === 'sm',
        'h-11 px-6 text-[15px]' => $size === 'md',
        'h-13 px-8 text-base' => $size === 'lg',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif

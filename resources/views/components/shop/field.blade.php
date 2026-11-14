{{--
    Champ de formulaire de la vitrine : libellé, saisie, aide et erreurs de validation (ids ARIA).
    La valeur se restitue après une erreur de validation (old()).
    <x-shop.field name="prenom" :label="__('messages.first_name')" :value="$client?->prenom" autocomplete="given-name" />
    Slot : le contrôle lui-même pour un <select> (<x-shop.field name="…" label="…"><select …>…</select></x-shop.field>).
--}}
@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => true,
    'hint' => null,
])

@php
    $id = 'champ-'.$name;
    $messages = (array) $errors->get($name);
    $describedBy = trim(($hint ? $id.'-aide ' : '').($messages ? $id.'-erreur' : ''));
    $control = 'h-11 w-full rounded-xl border bg-white px-4 text-sm placeholder:text-ink-muted focus:border-ink '
        .($messages ? 'border-danger' : 'border-sage');
@endphp

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-semibold">
        {{ $label }}
        @unless ($required)
            <span class="font-normal text-ink-muted">({{ __('messages.shop.optional') }})</span>
        @endunless
    </label>

    @if ($slot->isEmpty())
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}"
            value="{{ $type === 'password' ? '' : old($name, $value) }}" @required($required)
            @if ($messages) aria-invalid="true" @endif
            @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
            {{ $attributes->merge(['class' => $control]) }}>
    @else
        {{ $slot }}
    @endif

    @if ($hint)
        <p id="{{ $id }}-aide" class="mt-1 text-xs text-ink-muted">{{ $hint }}</p>
    @endif

    @if ($messages)
        <ul id="{{ $id }}-erreur" class="mt-1 space-y-0.5 text-sm text-danger">
            @foreach ($messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif
</div>

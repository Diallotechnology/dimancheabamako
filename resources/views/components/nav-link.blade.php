@props(['url'=>'','name' => ''])
@php
$classes = Route::currentRouteName() == $url ? 'active' : '';
@endphp

<li>
    <a href="{{ route($url) }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ __($name) }}
    </a>
</li>
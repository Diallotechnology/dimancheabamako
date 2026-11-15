{{--
    Métadonnées SEO / réseaux sociaux d'une page de la vitrine, à placer dans le slot `metadata` du layout.
    <x-slot:metadata><x-shop.seo :title="…" :description="…" :image="…" type="product" /></x-slot:metadata>
--}}
@props([
    'title',
    'description',
    'image' => null,
    'type' => 'website',
    'keywords' => null,
    'canonical' => null,
])

@php
    $image ??= asset('images/logo_meta_tag.png');
    $canonical ??= url()->current();
@endphp

<meta name="description" content="{{ $description }}">
@if ($keywords)
<meta name="keywords" content="{{ $keywords }}">
@endif
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:site_name" content="Dimanche à Bamako">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $image }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

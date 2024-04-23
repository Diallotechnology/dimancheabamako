<!doctype html>
<html lang="en-US">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description"
        content="E-commerce Dimanche à Bamako - Vente de Bazin teinté, Getzner Magnum, boubou et robes prêt-à-porter, brodés, wax et accessoires pour femme. Trouvez tout ce dont vous avez besoin ici.">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="Dimanche à bamako">
    <meta itemprop="description"
        content="E-commerce vente Bazin teinté, de Getzner Magnum, de boubou et robes prêt-à-porter, des brodés, wax et des accessoires pour femme">
    <meta itemprop="image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="https://www.dimancheabamako.com">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Dimanche à bamako">
    <meta property="og:description"
        content="E-commerce vente Bazin teinté, de Getzner Magnum, de boubou et robes prêt-à-porter, des brodés, wax et des accessoires pour femme">
    <meta property="og:image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Dimanche à bamako">
    <meta name="twitter:description"
        content="E-commerce vente Bazin teinté, de Getzner Magnum, de boubou et robes prêt-à-porter, des brodés, wax et des accessoires pour femme">
    <meta name="twitter:image" content="{{ asset('assets/imgs/theme/logo_meta_tag.png') }}">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/imgs/theme/favicon.svg') }}">
    <title>E-commerce Dimanche à Bamako - Vente de Bazin, Getzner Magnum, Boubou et Robes</title>

    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet" />
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    {{--
    <link href="{{ asset('build/assets/app-C5SX0j15.css') }}" rel="stylesheet" /> --}}
    {{-- <script type="module" src="{{ asset('build/assets/app-Cw44BPOK.js')}}"></script> --}}
    @inertiaHead
</head>

<body>
    @inertia
    <script src="{{ asset('assets/js/vendor/jquery-3.6.0.min.js')}}"></script>
    <script src="{{ asset('assets/js/vendor/modernizr-3.6.0.min.js')}}"></script>
    <script src="{{ asset('assets/js/vendor/jquery-migrate-3.3.0.min.js')}}"></script>
    <script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/slick.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/jquery.syotimer.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/wow.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/magnific-popup.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/images-loaded.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/isotope.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/scrollup.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/jquery.theia.sticky.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/jquery.elevatezoom.js')}}"></script>
    {{-- <script type="text/javascript"
        src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
    </script> --}}
</body>

</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet" />
</head>

<body>
    @include('layouts.nav')
    <div class="container mt-90">
        <div class="row">
            <div class="col-md-6 mx-auto">
                {{ $slot }}
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/slick.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/wow.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/images-loaded.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/isotope.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/scrollup.js') }}"></script>

</body>

</html>
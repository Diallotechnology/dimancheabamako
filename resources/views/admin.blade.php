<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name') }}</title>
    <link href="{{ asset('admin/assets/css/main.css') }}" rel="stylesheet" type="text/css" />
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    {{--
    <link href="{{ asset('build/assets/app-YaJoXqX7.css') }}" rel="stylesheet" /> --}}
    {{-- <script type="module" src="{{ asset('build/assets/app-5ifjTdLc.js')}}"></script> --}}
    @inertiaHead
</head>

<body>
    @inertia
    <script src="{{ asset('admin/assets/js/vendors/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendors/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendors/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendors/jquery.fullscreen.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendors/chart.js') }}"></script>
</body>

</html>

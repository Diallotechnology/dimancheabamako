<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/imgs/theme/favicon.svg">
    <title>@yield('title')</title>

    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet" />
</head>

<body>
    <main class="main page-404">
        <div class="container">
            <div class="row align-items-center height-100vh text-center">
                @yield('message')
            </div>
        </div>
    </main>
    <script src="{{ asset('assets/js/vendor/jquery-3.6.0.min.js')}}"></script>
    <script src="{{ asset('assets/js/vendor/modernizr-3.6.0.min.js')}}"></script>
    <script src="{{ asset('assets/js/vendor/jquery-migrate-3.3.0.min.js')}}"></script>
    <script src="{{ asset('assets/js/vendor/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/jquery.syotimer.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/wow.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/isotope.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/scrollup.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/jquery.theia.sticky.js')}}"></script>
    <script src="{{ asset('assets/js/plugins/jquery.elevatezoom.js')}}"></script>
</body>

</html>
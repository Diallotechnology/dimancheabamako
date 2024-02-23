@extends('errors::minimal')
@section('message')
<div class="col-lg-8 m-auto">
    {{-- <p class="mb-50"><img src="{{ asset('assets/imgs/theme/404.png') }}" alt="" class="hover-up"></p> --}}
    <h2 class="mb-30">Service Temporairement Indisponible</h2>
    <p class="font-lg text-grey-700 mb-30">
        Nous sommes désolés, mais le service est actuellement en cours de maintenance. Veuillez réessayer
        ultérieurement.
    </p>
</div>
@endsection
@section('title')
503
@endsection
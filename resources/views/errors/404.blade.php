@extends('errors::minimal')


@section('message')
<div class="col-lg-8 m-auto">
    <p class="mb-50"><img src="{{ asset('assets/imgs/theme/404.png') }}" alt="" class="hover-up"></p>
    <h2 class="mb-30">Page Not Found</h2>
    <p class="font-lg text-grey-700 mb-30">
        Le lien sur lequel vous avez cliqué est peut-être rompu ou la page a peut-être été supprimée.
        visitez la page d'accueil
    </p>
    <a class="btn btn-default submit-auto-width font-xs hover-up" href="{{ route('home') }}">Retour à la page
        d'accueil</a>
</div>
@endsection
@section('title')
404
@endsection
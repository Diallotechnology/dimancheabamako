@extends('errors.minimal')


@section('message')
<div class="col-lg-8 m-auto">
    <p class="mb-50"><img src="{{ asset('assets/imgs/theme/404.png') }}" alt="" class="hover-up"></p>
    <h2 class="mb-30">Page Not Found</h2>
    <p class="font-lg text-grey-700 mb-30">
        {{ __('messages.broken_link') }}
    </p>
    <a class="btn btn-default submit-auto-width font-xs hover-up" href="{{ route('home') }}">{{
        __('messages.back_to_home') }}</a>
</div>
@endsection
@section('title')
404
@endsection
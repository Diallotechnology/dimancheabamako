<x-app-layout>
    @php
    $cat = App\Models\Category::select('id','nom')->get();
    @endphp
    <x-slot:title>
        @lang('messages.category')
        </x-slot>
        <div class="page-header breadcrumb-wrap">
            <div class="container">
                <div class="breadcrumb">
                    <a href="/" rel="nofollow">@lang('messages.home')</a>
                    <span></span> Pages <span></span> @lang('messages.category')
                </div>
            </div>
        </div>
        <section class="section-padding">
            <div class="container pt-25">
                <h2 class="mb-3">Explorez nos categories de produits</h2>
                <div class="row">
                    <ul class="dropdown" style="list-style: inside;">
                        @foreach($cat as $row)
                        <li>
                            <a class="text-black-50 cat" style="font-size: x-large"
                                href="{{ route('shop', ['category'=>$row->id,'slug'=>$row->nom]) }}">{{
                                $row->nom }}</a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
        <style>
            .cat:hover {
                color: #d79f01 !important;
            }
        </style>
</x-app-layout>

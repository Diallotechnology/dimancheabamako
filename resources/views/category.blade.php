<x-app-layout>
    @php
    $cat = App\Models\Category::all();
    @endphp
    <div class="page-header breadcrumb-wrap">
        <div class="container">
            <div class="breadcrumb">
                <a href="/" rel="nofollow">Home</a>
                <span></span> Pages <span></span>Categories
            </div>
        </div>
    </div>
    <section class="section-padding">
        <div class="container pt-25">
            <div class="row">
                <ul class="dropdown" style="list-style: inside;">
                    @foreach($cat as $row)
                    <li>
                        <a class="text-black-50 cat" style="font-size: x-large" href="{{ route('shop', $row->id) }}">{{
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
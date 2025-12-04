<x-app-layout>

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
        <section class="py-4 bg-white">
            <div class="container">
                <h2 class="fs-5 fw-semibold text-dark mb-4">Catégories</h2>

                <div class="row g-3">
                    @foreach($categories as $row)
                    <div class="col-6">
                        <a href="{{ route('shop', ['category'=>$row->id, 'slug'=>$row->nom]) }}"
                            class="apple-card d-flex flex-column justify-content-center align-items-start p-3 rounded-4">
                            <span class="category-name">{{ $row->nom }}</span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <style>
            .apple-card {
                background: #f8f8f8;
                border: 1px solid #e5e5e5;
                border-radius: 18px;
                transition: all .2s ease;
            }

            .apple-card:hover {
                background: #ffffff;
                border-color: #d1d1d1;
                transform: translateY(-2px);
            }

            .category-name {
                font-size: 1rem;
                color: #000;
                font-weight: 500;
                letter-spacing: -0.2px;
            }
        </style>
</x-app-layout>
<x-app-layout>
    <x-slot:title>
        @lang('messages.about')
        </x-slot>
        <div class="page-header breadcrumb-wrap">
            <div class="container">
                <div class="breadcrumb">
                    <a href="/" rel="nofollow">@lang('messages.home')</a>
                    <span></span> Pages <span></span> @lang('messages.about')
                </div>
            </div>
        </div>
        <section class="section-padding">
            <div class="container pt-25">
                <div class="row">
                    <div class="col-lg-6 align-self-center mb-lg-0 mb-4">
                        <h2 class="mt-0 mb-15 text-uppercase font-sm text-brand wow fadeIn animated">
                            DIMANCHE A BAMAKO
                        </h2>
                        <h3 class="font-heading mb-40">@lang('messages.company_description')</h3>
                        <p>@lang('messages.company_history')</p>
                        <p>@lang('messages.team_description')</p>
                        <p>@lang('messages.purchase_instruction')</p>
                        <p>@lang('messages.showroom_description')</p>
                    </div>
                    <div class="col-lg-6">
                        <img src="{{ asset('assets/imgs/theme/about.jpg') }}" alt="about" />
                    </div>
                </div>
            </div>
        </section>
</x-app-layout>
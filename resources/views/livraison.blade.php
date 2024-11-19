<x-app-layout>
    <x-slot:title>
        @lang('messages.delivery')
        </x-slot>
        <section class="section-padding">
            <div class="container pt-25">
                <div class="row">
                    <div class="col-lg-7 align-self-center mb-lg-0 mb-4">
                        <div class="single-content m-5">

                            <h4>@lang('messages.delivery_conditions')</h4>
                            <p>@lang('messages.delivery_details') </p>
                            <p>@lang('messages.international_delivery')</p>
                            <p>@lang('messages.customs_fees')</p>
                            <h4>@lang('messages.contact_us')</h4>
                            <p>
                                DIMANCHE A BAMAKO <br />
                                ACI 2 000, près de la place CAN Bamako<br />
                                Malitel : +223 66 03 51 54
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <img src="{{ asset('assets/imgs/theme/DHL.jpg') }}" alt="dhl_logo" />
                    </div>
                </div>
            </div>
        </section>
</x-app-layout>
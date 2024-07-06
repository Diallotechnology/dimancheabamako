<x-app-layout>
    <section class="section-padding">
        <div class="container pt-25">
            <div class="row">
                <div class="col-md-6 m-auto">
                    <p class="mb-50"></p>
                    <h1 class="mb-4">{{ __('messages.operation_completed') }}.</h1>
                    <h4 class="mb-4">
                        {{ __('messages.order_success') }}
                    </h4>
                    <h4 class="mb-4">{{ __('messages.email_invoice') }}</h4>
                    <a class="btn btn-default submit-auto-width font-xs hover-up" href="{{ route('home') }}">
                        {{ __('messages.back_to_home') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
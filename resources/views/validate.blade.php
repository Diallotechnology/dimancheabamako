<x-app-layout>
    <section class="section-padding">
        <div class="container pt-25">
            <div class="row">
                <div class="col-md-6 m-auto">
                    <p class="mb-50"></p>
                    @if ($status === 'success')
                        <h1 class="mb-4">{{ __('messages.operation_completed') }}.</h1>
                        <h4 class="mb-4">
                            {{ __('messages.order_success') }}
                        </h4>
                        <h4 class="mb-4">{{ __('messages.email_invoice') }}</h4>
                    @elseif ($status === 'failed')
                        <h1 class="mb-4">{{ __('messages.payment_failed') }}</h1>
                        <h4 class="mb-4">{{ __('messages.payment_failed_hint') }}</h4>
                        <a class="btn btn-default submit-auto-width font-xs hover-up mb-3" href="{{ route('panier') }}">
                            {{ __('messages.back_to_cart') }}
                        </a>
                    @else
                        <h1 class="mb-4">{{ __('messages.payment_pending') }}</h1>
                        <h4 class="mb-4">{{ __('messages.payment_pending_hint') }}</h4>
                    @endif
                    <a class="btn btn-default submit-auto-width font-xs hover-up" href="{{ route('home') }}">
                        {{ __('messages.back_to_home') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>

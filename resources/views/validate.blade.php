<x-shop-layout>
    <x-slot:title>{{ $status === 'success' ? __('messages.operation_completed') : ($status === 'failed' ? __('messages.payment_failed') : __('messages.payment_pending')) }}</x-slot:title>

    <div class="container-shop py-12 md:py-20">
        <div class="mx-auto max-w-xl text-center" role="status">
            @if ($status === 'success')
                <h1 class="text-3xl md:text-4xl">{{ __('messages.operation_completed') }}</h1>
                <p class="mt-4 text-lg">{{ __('messages.order_success') }}</p>
                <p class="mt-2 text-ink-muted">{{ __('messages.email_invoice') }}</p>
            @elseif ($status === 'failed')
                <h1 class="text-3xl md:text-4xl">{{ __('messages.payment_failed') }}</h1>
                <p class="mt-4 text-ink-muted">{{ __('messages.payment_failed_hint') }}</p>
            @else
                <h1 class="text-3xl md:text-4xl">{{ __('messages.payment_pending') }}</h1>
                <p class="mt-4 text-ink-muted">{{ __('messages.payment_pending_hint') }}</p>
            @endif

            @if ($accountPending)
                <p class="mt-6 rounded-lg bg-brand-soft px-4 py-3 text-sm">{{ __('messages.account_confirmation_pending') }}</p>
            @endif

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @if ($status === 'failed')
                    <x-shop.button :href="route('panier')" size="lg">{{ __('messages.back_to_cart') }}</x-shop.button>
                @endif
                <x-shop.button :href="route('home')" size="lg" :variant="$status === 'failed' ? 'outline' : 'primary'">{{ __('messages.back_to_home') }}</x-shop.button>
            </div>
        </div>
    </div>
</x-shop-layout>

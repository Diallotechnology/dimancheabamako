<x-shop-layout>
    <x-slot:title>{{ __('messages.register') }}</x-slot:title>

    <div class="container-shop py-10 md:py-16">
        <div class="mx-auto max-w-md space-y-6 text-center">
            @if (session()->has('status'))
                <p role="status" class="rounded-lg bg-success/10 px-4 py-3 font-medium text-success">{{ session('status') }}</p>
            @endif

            @if ($show)
                <x-shop.button :href="route('home')" size="lg">{{ __('messages.back_to_home') }}</x-shop.button>
            @endif
        </div>
    </div>
</x-shop-layout>

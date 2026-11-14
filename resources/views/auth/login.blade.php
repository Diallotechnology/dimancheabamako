<x-shop-layout>
    <x-slot:title>{{ __('messages.login') }}</x-slot:title>

    <x-shop.auth-card :title="__('messages.login')">
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <x-shop.field name="email" type="email" label="Email" autocomplete="username" autofocus />
            <x-shop.field name="password" type="password" :label="__('messages.password')" autocomplete="current-password" />

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="inline-block text-sm underline hover:text-brand-strong">
                    {{ __('messages.forgot_password') }}
                </a>
            @endif

            <x-shop.button type="submit" size="lg" class="w-full">{{ __('messages.login') }}</x-shop.button>
        </form>

        <p class="mt-6 text-center text-sm text-ink-muted">
            {{ __('messages.no_account') }}
            <a href="{{ route('register') }}" class="font-semibold text-ink underline hover:text-brand-strong">{{ __('messages.register') }}</a>
        </p>
    </x-shop.auth-card>
</x-shop-layout>

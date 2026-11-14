<x-shop-layout>
    <x-slot:title>{{ __('messages.forgot_password') }}</x-slot:title>

    <x-shop.auth-card :title="__('messages.forgot_password')" :intro="__('messages.forgot_password_note')">
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <x-shop.field name="email" type="email" label="Email" autocomplete="email" autofocus />
            <x-shop.button type="submit" size="lg" class="w-full">{{ __('messages.shop.send') }}</x-shop.button>
        </form>
    </x-shop.auth-card>
</x-shop-layout>

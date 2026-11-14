<x-shop-layout>
    <x-slot:title>{{ __('Confirm') }}</x-slot:title>

    <x-shop.auth-card :title="__('Confirm')" :intro="__('This is a secure area of the application. Please confirm your password before continuing.')">
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf
            <x-shop.field name="password" type="password" :label="__('messages.password')" autocomplete="current-password" autofocus />
            <x-shop.button type="submit" size="lg" class="w-full">{{ __('Confirm') }}</x-shop.button>
        </form>
    </x-shop.auth-card>
</x-shop-layout>

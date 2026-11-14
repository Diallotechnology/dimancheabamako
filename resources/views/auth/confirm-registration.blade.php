<x-shop-layout>
    <x-slot:title>{{ __('messages.confirm_account_title') }}</x-slot:title>

    <x-shop.auth-card :title="__('messages.confirm_account_title')" :intro="__('messages.confirm_account_hint')">
        <form method="POST" action="{{ route('confirm-registration.store', $token) }}">
            @csrf
            <x-shop.button type="submit" size="lg" class="w-full">{{ __('messages.confirm_account_button') }}</x-shop.button>
        </form>
    </x-shop.auth-card>
</x-shop-layout>

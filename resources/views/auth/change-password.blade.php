<x-shop-layout>
    <x-slot:title>{{ __('messages.password_change_required') }}</x-slot:title>

    <x-shop.auth-card :title="__('messages.password_change_required')" :intro="__('messages.password_note')">
        @error('email')
            <p role="alert" class="mb-4 rounded-lg bg-danger/10 px-3 py-2 text-sm font-medium text-danger">{{ $message }}</p>
        @enderror
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form action="{{ $action }}" method="post" class="space-y-4">
            @csrf
            <x-shop.field name="password" type="password" :label="__('messages.new_password')" autocomplete="new-password" autofocus />
            <x-shop.field name="password_confirmation" type="password" :label="__('messages.confirm_password')" autocomplete="new-password" />

            <x-shop.button type="submit" size="lg" class="w-full">{{ __('messages.shop.send') }}</x-shop.button>
        </form>

        <p class="mt-6 text-center text-sm">
            <a href="{{ route('login') }}" class="underline hover:text-brand-strong">{{ __('messages.login') }}</a>
        </p>
    </x-shop.auth-card>
</x-shop-layout>

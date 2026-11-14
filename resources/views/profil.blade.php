@php
    $user = Auth::user();
    $client = $user->client;
@endphp

<x-shop-layout>
    <x-slot:title>{{ __('messages.shop.profile') }}</x-slot:title>

    <x-shop.page-header :title="__('messages.shop.profile')" />

    <div class="container-shop grid gap-8 py-10 md:py-14 lg:grid-cols-3 lg:gap-12">
        <section aria-labelledby="compte-titre" class="lg:col-span-2">
            <h2 id="compte-titre" class="text-xl">{{ __('messages.shop.account_details') }}</h2>

            @if (session()->has('success'))
                <p role="status" class="mt-4 rounded-lg bg-success/10 px-3 py-2 text-sm font-medium text-success">{{ session('success') }}</p>
            @endif

            <form method="post" action="{{ route('profil.update', $user) }}" class="mt-6 grid gap-5">
                @csrf
                @method('PUT')

                <x-shop.field name="name" :label="__('messages.shop.username')" :value="$user->name" autocomplete="username" />
                <x-shop.field name="email" type="email" label="Email" :value="$user->email" autocomplete="email" />

                <div class="grid gap-5 border-t border-sage pt-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-shop.field name="current_password" type="password" :label="__('messages.current_password')" :required="false" autocomplete="current-password" />
                    </div>
                    <x-shop.field name="password" type="password" :label="__('messages.new_password')" :required="false" autocomplete="new-password" />
                    <x-shop.field name="password_confirmation" type="password" :label="__('messages.confirm_password')" :required="false" autocomplete="new-password" />
                </div>

                <div>
                    <x-shop.button type="submit" size="lg" class="w-full sm:w-auto">{{ __('messages.shop.save') }}</x-shop.button>
                </div>
            </form>
        </section>

        <aside aria-labelledby="adresse-titre" class="self-start rounded-2xl border border-sage bg-linen p-5">
            <h2 id="adresse-titre" class="text-xl">{{ __('messages.address') }}</h2>
            <address class="mt-3 space-y-1 text-sm not-italic text-ink-muted">
                <span class="block">{{ $client?->email }}</span>
                <span class="block">{{ __('messages.contact') }} : {{ $client?->contact }}</span>
                <span class="block">{{ __('messages.country') }} : {{ $client?->pays }}</span>
            </address>
        </aside>
    </div>
</x-shop-layout>

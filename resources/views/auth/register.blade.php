<x-shop-layout>
    <x-slot:title>{{ __('messages.register') }}</x-slot:title>

    <x-shop.auth-card :title="__('messages.register')" width="max-w-2xl">
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('register') }}" class="grid gap-5 sm:grid-cols-2">
            @csrf
            <x-shop.honeypot />

            <x-shop.field name="prenom" :label="__('messages.first_name')" autocomplete="given-name" autofocus />
            <x-shop.field name="nom" :label="__('messages.last_name')" autocomplete="family-name" />

            <x-shop.field name="pays" :label="__('messages.country')">
                <select id="champ-pays" name="pays" required
                    @if ($errors->has('pays')) aria-invalid="true" aria-describedby="champ-pays-erreur" @endif
                    class="h-11 w-full rounded-xl border bg-white px-4 text-sm focus:border-ink {{ $errors->has('pays') ? 'border-danger' : 'border-sage' }}">
                    <option value="" disabled @selected(old('pays') === null)>{{ __('messages.select_country') }}</option>
                    @foreach ($pays as $row)
                        <option value="{{ $row }}" @selected(old('pays') === $row)>{{ $row }}</option>
                    @endforeach
                </select>
            </x-shop.field>
            <x-shop.field name="contact" type="tel" :label="__('messages.contact_with_code')" placeholder="+223XXXXXXXX" autocomplete="tel" />

            <div class="sm:col-span-2">
                <x-shop.field name="email" type="email" label="Email" autocomplete="username" />
            </div>
            <x-shop.field name="password" type="password" :label="__('messages.password')" autocomplete="new-password" />
            <x-shop.field name="password_confirmation" type="password" :label="__('messages.confirm_password')" autocomplete="new-password" />

            <div class="sm:col-span-2">
                <x-shop.button type="submit" size="lg" class="w-full">{{ __('messages.register') }}</x-shop.button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-ink-muted">
            {{ __('messages.already_have_account') }}
            <a href="{{ route('login') }}" class="font-semibold text-ink underline hover:text-brand-strong">{{ __('messages.login') }}</a>
        </p>
    </x-shop.auth-card>
</x-shop-layout>

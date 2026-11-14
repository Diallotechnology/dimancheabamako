<x-shop-layout>
    <x-slot:title>{{ __('messages.contact_us') }}</x-slot:title>

    <x-slot:metadata>
        <x-shop.seo :title="'Dimanche à Bamako - '.__('messages.contact_us')" :description="__('messages.shop.address_value').' · +223 66 03 51 54'"
            :canonical="route('contact')" />
    </x-slot:metadata>

    <x-shop.page-header :title="__('messages.contact_us')" />

    <div class="container-shop grid gap-10 py-10 md:py-16 lg:grid-cols-3 lg:gap-14">
        <section aria-labelledby="boutique-titre" class="space-y-4">
            <h2 id="boutique-titre" class="text-2xl">{{ __('messages.shop.store') }}</h2>
            <address class="not-italic text-ink-muted">
                {{ __('messages.shop.address_value') }}<br>
                <a href="tel:+22366035154" class="font-semibold text-ink hover:text-brand-strong">+223 66 03 51 54</a>
            </address>
            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(__('messages.shop.address_value')) }}" target="_blank" rel="noopener"
                class="inline-flex h-11 items-center gap-2 rounded-full border border-ink/20 px-5 text-sm font-semibold hover:border-brand hover:bg-brand">
                <x-shop.icon name="map-pin" class="size-4" />
                {{ __('messages.shop.see_map') }}
                <span class="sr-only">({{ __('messages.shop.new_tab') }})</span>
            </a>
        </section>

        <section aria-labelledby="formulaire-titre" class="lg:col-span-2">
            <h2 id="formulaire-titre" class="text-2xl">{{ __('messages.contact_us') }}</h2>

            <form method="post" action="{{ route('contact.email') }}" class="mt-6 grid gap-5 sm:grid-cols-2">
                @csrf
                <x-shop.honeypot />
                <x-shop.field name="name" :label="__('messages.shop.contact_name')" autocomplete="name" />
                <x-shop.field name="email" type="email" :label="__('messages.shop.contact_email')" autocomplete="email" />
                <div class="sm:col-span-2">
                    <x-shop.field name="subject" :label="__('messages.shop.contact_subject')" />
                </div>
                <div class="sm:col-span-2">
                    <x-shop.field name="message" :label="__('messages.shop.contact_message')">
                        <textarea id="champ-message" name="message" rows="6" required
                            @if ($errors->has('message')) aria-invalid="true" aria-describedby="champ-message-erreur" @endif
                            class="w-full rounded-xl border bg-white px-4 py-3 text-sm focus:border-ink {{ $errors->has('message') ? 'border-danger' : 'border-sage' }}">{{ old('message') }}</textarea>
                    </x-shop.field>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit"
                        class="inline-flex h-12 w-full items-center justify-center rounded-full bg-brand px-10 font-semibold transition-colors hover:bg-ink hover:text-white active:scale-[0.98] sm:w-auto">
                        {{ __('messages.shop.send') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-shop-layout>

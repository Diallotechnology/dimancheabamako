<x-shop-layout>
    <x-slot:title>{{ __('messages.delivery') }}</x-slot:title>

    <x-slot:metadata>
        <x-shop.seo :title="'Dimanche à Bamako - '.__('messages.delivery')" :description="__('messages.delivery_details')"
            :canonical="route('livraison')" />
    </x-slot:metadata>

    <x-shop.page-header :title="__('messages.delivery')" />

    <section class="container-shop grid items-center gap-8 py-10 md:grid-cols-5 md:py-16 lg:gap-14">
        <div class="space-y-4 md:col-span-3">
            <h2 class="text-2xl">{{ __('messages.delivery_conditions') }}</h2>
            <p>{{ __('messages.delivery_details') }}</p>
            <p>{{ __('messages.international_delivery') }}</p>
            <p>{{ __('messages.customs_fees') }}</p>

            <h2 class="pt-4 text-2xl">{{ __('messages.contact_us') }}</h2>
            <address class="not-italic">
                Dimanche à Bamako<br>
                {{ __('messages.shop.address_value') }}<br>
                <a href="tel:+22366035154" class="font-semibold hover:text-brand-strong">+223 66 03 51 54</a>
            </address>
        </div>
        <img src="{{ asset('assets/imgs/theme/DHL.jpg') }}" alt="DHL" loading="lazy" width="600" height="400"
            class="w-full rounded-2xl bg-sage-soft object-contain md:col-span-2">
    </section>
</x-shop-layout>

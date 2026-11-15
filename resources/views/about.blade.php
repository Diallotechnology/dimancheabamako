<x-shop-layout>
    <x-slot:title>{{ __('messages.about') }}</x-slot:title>

    <x-slot:metadata>
        <x-shop.seo :title="'Dimanche à Bamako - '.__('messages.about')" :description="__('messages.company_description')"
            :canonical="route('about')" />
    </x-slot:metadata>

    <x-shop.page-header :title="__('messages.about')" />

    <section class="container-shop grid items-center gap-8 py-10 md:grid-cols-2 md:py-16 lg:gap-14">
        <div class="space-y-4">
            <p class="text-sm font-semibold tracking-wide text-brand-strong uppercase">Dimanche à Bamako</p>
            <h2 class="text-2xl md:text-3xl">{{ __('messages.company_description') }}</h2>
            <p>{{ __('messages.company_history') }}</p>
            <p>{{ __('messages.team_description') }}</p>
            <p>{{ __('messages.purchase_instruction') }}</p>
            <p>{{ __('messages.showroom_description') }}</p>
        </div>
        <img src="{{ asset('images/about.jpg') }}" alt="" loading="lazy" width="800" height="600"
            class="w-full rounded-2xl bg-sage-soft object-cover">
    </section>
</x-shop-layout>

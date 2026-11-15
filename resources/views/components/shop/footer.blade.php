{{-- Pied de page de la vitrine (layout `shop`). --}}
<footer class="mt-16 border-t border-sage bg-linen text-sm text-ink-muted">
    <div class="container-shop grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2 lg:col-span-1">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="{{ asset('images/logo.svg') }}" alt="Dimanche à Bamako" class="h-10 w-auto"
                    width="150" height="40" loading="lazy">
            </a>
            <p class="mt-4 max-w-xs leading-relaxed">{{ __('messages.bazin_sale') }}</p>
        </div>

        <div>
            <h2 class="mb-4 text-base text-ink">{{ __('messages.contact') }}</h2>
            <ul class="space-y-3">
                <li class="flex gap-2.5">
                    <x-shop.icon name="map-pin" class="mt-0.5 size-4 text-brand-strong" />
                    <span><span class="sr-only">{{ __('messages.address') }} : </span>{{ __('messages.shop.address_value') }}</span>
                </li>
                <li class="flex gap-2.5">
                    <x-shop.icon name="phone" class="mt-0.5 size-4 text-brand-strong" />
                    <a href="tel:+22366035154" class="hover:text-ink">+223 66 03 51 54</a>
                </li>
                <li class="flex gap-2.5">
                    <x-shop.icon name="clock" class="mt-0.5 size-4 text-brand-strong" />
                    <span><span class="sr-only">{{ __('messages.shop.opening_hours') }} : </span>{{ __('messages.shop.opening_hours_value') }}</span>
                </li>
            </ul>
        </div>

        <div>
            <h2 class="mb-4 text-base text-ink">Dimanche à Bamako</h2>
            <ul class="space-y-2.5">
                <li><a href="{{ route('about') }}" class="hover:text-ink">{{ __('messages.about') }}</a></li>
                <li><a href="{{ route('livraison') }}" class="hover:text-ink">{{ __('messages.delivery') }}</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-ink">{{ __('messages.contact') }}</a></li>
            </ul>
        </div>

        <div>
            <h2 class="mb-4 text-base text-ink">{{ __('messages.shop.follow_us') }}</h2>
            <div class="flex gap-3">
                <a href="https://www.facebook.com/share/16vqoDjtmp/" target="_blank" rel="noopener"
                    class="grid size-10 place-items-center rounded-full border border-sage bg-white transition-colors hover:border-brand">
                    <img src="{{ asset('images/icon-facebook.svg') }}" alt="Facebook" class="size-4" loading="lazy">
                </a>
            </div>

            <h2 class="mt-8 mb-3 flex items-center gap-2 text-base text-ink">
                <x-shop.icon name="lock" class="size-4" /> {{ __('messages.shop.secure_payment') }}
            </h2>
            <img src="{{ asset('images/payment-method.png') }}" alt="{{ __('messages.shop.secure_payment') }}"
                class="h-auto max-w-[220px]" loading="lazy">
        </div>
    </div>

    <div class="border-t border-sage">
        <div class="container-shop flex flex-col gap-1 py-5 text-xs sm:flex-row sm:justify-between">
            <p>&copy; {{ date('Y') }} <span class="font-semibold text-ink">Dimanche à Bamako</span>.
                {{ __('messages.shop.all_rights_reserved') }}</p>
        </div>
    </div>
</footer>

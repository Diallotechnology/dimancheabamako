<div>
    <x-slot:title>{{ __('messages.cart') }}</x-slot:title>

    @php
        // Client connu : on ne lui redemande ni son identité ni son adresse (modifiables à la demande).
        $lastOrder = $client?->latestOrder;
        $known = $client && $client->prenom && $client->nom && $client->email && $client->contact
            && $lastOrder?->adresse && $lastOrder?->ville;
    @endphp

    <div class="border-b border-sage bg-linen">
        <div class="container-shop py-8 md:py-10">
            <h1 class="text-2xl md:text-4xl">{{ __('messages.cart') }}</h1>
            <p class="mt-1 text-ink-muted" role="status" aria-live="polite">
                {{ trans_choice('messages.shop.results_count', $count) }}
            </p>
        </div>
    </div>

    <div class="container-shop py-8 md:py-12">
        @if ($items->isEmpty())
            <div class="py-16 text-center">
                <p class="text-lg text-ink-muted">{{ __('messages.no_products_in_cart') }}</p>
                <a href="{{ route('categorie_mobile') }}"
                    class="mt-6 inline-flex h-12 items-center rounded-full bg-brand px-8 font-semibold hover:bg-ink hover:text-white">
                    {{ __('messages.continue_shopping') }}
                </a>
            </div>
        @else
            <div class="grid gap-8 lg:grid-cols-3 lg:gap-12">
                {{-- Lignes du panier --}}
                <section aria-labelledby="lignes-titre" class="lg:col-span-2">
                    <h2 id="lignes-titre" class="sr-only">{{ __('messages.shop.cart_lines') }}</h2>
                    <ul class="divide-y divide-sage border-y border-sage">
                        @foreach ($items as $item)
                            <li wire:key="ligne-{{ $item['id'] }}" class="flex items-center gap-4 py-4">
                                <img src="{{ $item->get('attributes')->get('cover') }}" alt="" loading="lazy"
                                    class="size-20 shrink-0 rounded-xl bg-sage-soft object-cover sm:size-24">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-display font-semibold">{{ $item['name'] }}</p>
                                    <p class="mt-0.5 text-sm text-ink-muted">
                                        {{ __('messages.quantity') }} : {{ $item['quantity'] }}
                                        @if ($item['is_preorder'])
                                            {{ __('messages.product_status.unit') }}
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm">
                                        <span class="text-ink-muted">{{ __('messages.unit_price') }} :</span>
                                        <x-shop.price :amount="$item['price']" />
                                    </p>
                                </div>
                                <button type="button" wire:click="remove({{ (int) $item['id'] }})" wire:loading.attr="disabled"
                                    wire:target="remove({{ (int) $item['id'] }})"
                                    class="shrink-0 rounded-full border border-sage p-2.5 text-ink-muted transition-colors hover:border-danger hover:text-danger disabled:opacity-50">
                                    <x-shop.icon name="x" />
                                    <span class="sr-only">{{ __('messages.shop.remove_item', ['name' => $item['name']]) }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </section>

                {{-- Récapitulatif : sous les lignes sur mobile, à droite (collant) sur grand écran. --}}
                <aside aria-labelledby="recap-titre" class="lg:col-start-3 lg:row-span-2 lg:row-start-1">
                    <div class="rounded-2xl border border-sage bg-linen p-5 lg:sticky lg:top-24">
                        <h2 id="recap-titre" class="text-xl">{{ __('messages.shop.summary') }}</h2>

                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink-muted">{{ __('messages.total_weight') }}</dt>
                                <dd class="font-semibold">{{ $totalWeight }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink-muted">{{ __('messages.total_quantity') }}</dt>
                                <dd class="font-semibold">{{ $TotalQuantity }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink-muted">{{ __('messages.shipping') }}</dt>
                                <dd class="font-semibold">
                                    @if ($shipping)
                                        {{ \App\Service\PriceService::format($shipping->montant) }}
                                    @else
                                        <span class="font-normal text-ink-muted">—</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-ink-muted">{{ __('messages.delivery_time') }}</dt>
                                <dd class="font-semibold">{{ __('messages.delivery_days') }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-4 border-t border-sage pt-3">
                                <dt class="font-semibold">Total</dt>
                                <dd class="font-display text-2xl font-bold">{{ $this->totalWithShipping }}</dd>
                            </div>
                        </dl>

                        <p class="mt-4 text-xs text-ink-muted">{{ __('messages.payment_note') }}</p>

                        @if ($this->hasPreorder)
                            <p class="mt-3 rounded-lg bg-brand-soft px-3 py-2 text-sm">{{ __('messages.preorder_notice') }}</p>
                        @endif

                        @if (session()->has('warning'))
                            <p role="alert" class="mt-3 rounded-lg bg-danger/10 px-3 py-2 text-sm font-medium text-danger">
                                {{ session('warning') }}
                            </p>
                        @endif
                    </div>
                </aside>

                {{-- Commande --}}
                <section aria-labelledby="commande-titre" class="lg:col-span-2" x-data="{ login: false, account: {{ old('password') ? 'true' : 'false' }}, edit: {{ $known && ! $errors->any() ? 'false' : 'true' }} }">
                    <h2 id="commande-titre" class="text-xl">{{ __('messages.order_information') }}</h2>

                    @guest
                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">
                            <p class="text-sm text-ink-muted">{{ __('messages.already_have_account') }}</p>
                            <button type="button" @click="login = true"
                                class="inline-flex h-10 items-center gap-2 rounded-full border border-ink/20 px-5 text-sm font-semibold hover:border-brand hover:bg-brand">
                                <x-shop.icon name="user" class="size-4" />
                                {{ __('messages.login') }}
                            </button>
                        </div>

                        {{-- Connexion : boîte de dialogue Alpine (focus piégé, Échap pour fermer). --}}
                        <div x-cloak x-show="login" x-on:keydown.escape.window="login = false"
                            class="fixed inset-0 z-[70] grid place-items-center p-4" role="dialog" aria-modal="true"
                            aria-labelledby="login-titre">
                            <div x-show="login" x-transition.opacity class="absolute inset-0 bg-ink/50" @click="login = false"></div>
                            <div x-show="login" x-trap.noscroll="login" class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-pop">
                                <button type="button" @click="login = false"
                                    class="absolute top-3 right-3 rounded-full p-2 text-ink-muted hover:bg-sage-soft hover:text-ink">
                                    <x-shop.icon name="x" />
                                    <span class="sr-only">{{ __('messages.shop.close') }}</span>
                                </button>
                                <h2 id="login-titre" class="text-xl">{{ __('messages.shop.login_title') }}</h2>

                                <form wire:submit="login_submit" class="mt-5 space-y-4">
                                    <div>
                                        <label for="login-email" class="mb-1.5 block text-sm font-semibold">{{ __('Email') }}</label>
                                        <input id="login-email" type="email" wire:model="email" required autocomplete="username"
                                            @error('email') aria-invalid="true" aria-describedby="login-email-erreur" @enderror
                                            class="h-11 w-full rounded-xl border border-sage px-4 text-sm focus:border-ink">
                                        @error('email')
                                            <p id="login-email-erreur" class="mt-1 text-sm text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="login-password" class="mb-1.5 block text-sm font-semibold">{{ __('messages.password') }}</label>
                                        <input id="login-password" type="password" wire:model="password" required autocomplete="current-password"
                                            @error('password') aria-invalid="true" aria-describedby="login-password-erreur" @enderror
                                            class="h-11 w-full rounded-xl border border-sage px-4 text-sm focus:border-ink">
                                        @error('password')
                                            <p id="login-password-erreur" class="mt-1 text-sm text-danger">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="inline-block text-sm underline hover:text-brand-strong">
                                            {{ __('messages.forgot_password') }}
                                        </a>
                                    @endif
                                    <button type="submit" wire:loading.attr="disabled" wire:target="login_submit"
                                        class="inline-flex h-12 w-full items-center justify-center rounded-full bg-brand font-semibold hover:bg-ink hover:text-white disabled:opacity-60">
                                        <span wire:loading.remove wire:target="login_submit">{{ __('messages.login') }}</span>
                                        <span wire:loading wire:target="login_submit">{{ __('messages.shop.processing') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest

                    {{-- POST classique vers OrderController@store (validation serveur) ; wire:ignore : la saisie survit aux rendus Livewire. --}}
                    <form method="post" action="{{ route('order.store') }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="livraison" value="{{ $shipping?->id }}">

                        @if ($known)
                            <div x-show="! edit" class="rounded-2xl border border-sage p-4 text-sm">
                                <p class="font-semibold">{{ $client->prenom }} {{ $client->nom }}</p>
                                <p class="mt-1 text-ink-muted">{{ $lastOrder->adresse }}, {{ $lastOrder->postal }} {{ $lastOrder->ville }}</p>
                                <p class="text-ink-muted">{{ $client->contact }} · {{ $client->email }}</p>
                                <button type="button" @click="edit = true" class="mt-3 text-sm font-semibold underline hover:text-brand-strong">
                                    {{ __('messages.shop.edit') }}
                                </button>
                            </div>
                        @endif

                        <div x-show="edit" class="grid gap-5 sm:grid-cols-2">
                            <div wire:ignore><x-shop.field name="prenom" :label="__('messages.first_name')" :value="$client?->prenom" autocomplete="given-name" /></div>
                            <div wire:ignore><x-shop.field name="nom" :label="__('messages.last_name')" :value="$client?->nom" autocomplete="family-name" /></div>
                            <div wire:ignore><x-shop.field name="adresse" :label="__('messages.address')" :value="$client?->latestOrder?->adresse" autocomplete="street-address" /></div>
                            <div wire:ignore><x-shop.field name="postal" label="Postal" :required="false" :value="$client?->latestOrder?->postal" autocomplete="postal-code" /></div>
                            <div wire:ignore><x-shop.field name="ville" :label="__('messages.city')" :value="$client?->latestOrder?->ville" autocomplete="address-level2" /></div>
                            <div wire:ignore><x-shop.field name="contact" type="tel" :label="__('messages.contact_with_code')" :value="$client?->contact" placeholder="+223XXXXXXXX" autocomplete="tel" /></div>
                            <div wire:ignore><x-shop.field name="email" type="email" label="Email" :value="$client?->email" autocomplete="email" /></div>
                        </div>

                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            <x-shop.field name="country_id" :label="__('messages.delivery_country')" :hint="__('messages.select_country_first')">
                                <select id="champ-country_id" name="country_id" required wire:model.live="country_id"
                                    class="h-11 w-full rounded-xl border border-sage bg-white px-4 text-sm focus:border-ink">
                                    <option value="">{{ __('messages.select_country') }}</option>
                                    @foreach ($country as $pays)
                                        <option value="{{ $pays->id }}">{{ $pays->nom }}</option>
                                    @endforeach
                                </select>
                            </x-shop.field>

                            <x-shop.field name="transport_id" :label="__('messages.carrier')">
                                <select id="champ-transport_id" name="transport_id" required wire:model.live="transport_id"
                                    class="h-11 w-full rounded-xl border border-sage bg-white px-4 text-sm focus:border-ink">
                                    <option value="">{{ __('messages.select_carrier') }}</option>
                                    @foreach ($trans as $transport)
                                        <option value="{{ $transport->id }}">{{ $transport->nom }}</option>
                                    @endforeach
                                </select>
                            </x-shop.field>
                        </div>

                        @error('livraison')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror

                        @guest
                            <div class="mt-6">
                                <label class="flex items-center gap-3 text-sm font-semibold">
                                    <input type="checkbox" x-model="account" class="size-5 rounded border-sage accent-brand">
                                    {{ __('messages.create_account_prompt') }}
                                </label>
                                <div x-cloak x-show="account" class="mt-4 max-w-md" wire:ignore>
                                    <x-shop.field name="password" type="password" :label="__('messages.password')" :required="false"
                                        :hint="__('messages.shop.password_hint')" autocomplete="new-password" />
                                </div>
                            </div>
                        @endguest

                        <div class="mt-6" wire:ignore>
                            <label for="champ-commentaire" class="mb-1.5 block text-sm font-semibold">{{ __('messages.add_comments_prompt') }}</label>
                            <textarea id="champ-commentaire" name="commentaire" rows="4" placeholder="{{ __('messages.shop.comments_placeholder') }}"
                                class="w-full rounded-xl border border-sage px-4 py-3 text-sm focus:border-ink">{{ old('commentaire') }}</textarea>
                        </div>

                        <button type="submit"
                            class="mt-8 inline-flex h-12 w-full items-center justify-center rounded-full bg-brand px-10 font-semibold transition-colors hover:bg-ink hover:text-white active:scale-[0.98] sm:w-auto">
                            {{ __('messages.shop.place_order') }}
                        </button>
                    </form>
                </section>
            </div>
        @endif
    </div>
</div>

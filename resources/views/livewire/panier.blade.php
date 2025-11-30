<div>
    @php
    if (Auth::check() and Auth::user()->isClient()) {
    $client = App\Models\Client::where('email', Auth::user()->email)->first();
    } else {
    $client = null;
    }

    @endphp
    <x-slot:title>
        {{ __('messages.cart')}}
        </x-slot>
        <section class="mt-50 mb-50">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-20">
                            <h4>
                                @lang('messages.cart') {{ $count }} element(s)
                            </h4>
                        </div>
                        <div class="table-responsive order_table text-center">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>@lang('messages.product')</th>
                                        <th>@lang('messages.quantity')</th>
                                        <th>@lang('messages.unit_price')</th>
                                        <th>@lang('messages.action')</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse ($items as $item)
                                    <tr wire:key="{{ $item['id'] }}">
                                        <td class="image product-thumbnail">
                                            <img src="{{ $item->get('attributes')->get('cover') }}"
                                                alt="{{ $item['name'] }}" />
                                            <h5>{{ $item['name'] }}</h5>
                                        </td>
                                        <td class="text-center" data-title="Stock">
                                            <div class="border radius d-inline-flex">
                                                <livewire:update :row="$item" :key="'update-'.$item['id']" />
                                            </div>
                                        </td>
                                        <td>
                                            {{ number_format(
                                            session('devise') === 'EUR'
                                            ? ($item['price'] / session('taux_eur'))
                                            : $item['price'],
                                            session('devise') === 'EUR' ? 2 : 0,
                                            ',',
                                            ' '
                                            ) }} {{ session('devise') === 'EUR' ? '€' : 'CFA' }}
                                        </td>
                                        <td class="action" data-title="Remove">
                                            <livewire:delete :row="$item" :key="'delete-'.$item['id']" />
                                        </td>
                                    </tr>
                                    @empty
                                    <h4 class="text-center my-5">
                                        @lang('messages.no_products_in_cart')
                                    </h4>
                                    @endforelse
                                </tbody>
                            </table>

                        </div>
                    </div>
                    <div @class(['col-md-4 order-1', 'd-none'=> $items->count() == 0])>
                        <h5>@lang('messages.payment_note')</h5>
                        <div class="table-responsive order_table text-center sticky-top">
                            <table class="table">
                                <tr>
                                    <th>@lang('messages.total_weight')</th>
                                    <td>
                                        <em>{{ $totalWeight }}</em>
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('messages.shipping')</th>
                                    <td>
                                        <em>
                                            @isset($shipping)
                                            @php
                                            $montant = is_numeric($shipping->montant_devise) ? $shipping->montant_devise
                                            :
                                            0;
                                            @endphp
                                            {{ session('devise') === 'EUR' ? $montant . ' €' : $montant . ' CFA' }}
                                            @endisset

                                        </em>
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('messages.delivery_time')</th>
                                    <td>
                                        <em>@lang('messages.delivery_days')</em>
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('messages.total_quantity')</th>
                                    <td>
                                        <em>{{ $TotalQuantity }}</em>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td class="product-subtotal">
                                        <span class="font-xl text-brand fw-900">
                                            @isset($shipping)
                                            @php
                                            $totalAmount = 0;
                                            if (is_numeric($Total) && is_numeric($shipping->montant_devise)) {
                                            $totalAmount = $Total + $shipping->montant_devise;
                                            } else {
                                            $totalAmount = $Total;
                                            }
                                            @endphp
                                            {{ session('devise') === 'EUR' ? $totalAmount . ' €' : $totalAmount . ' CFA'
                                            }}
                                            @else
                                            {{ session('devise') === 'EUR' ? $Total . ' €' : $Total . ' CFA' }}
                                            @endisset
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div @class(['col-md-8', 'd-none'=> $items->count() == 0]) >
                        <div class="mb-25">
                            <h4>@lang('messages.order_information')</h4>
                            @guest
                            <h5 class="py-2">@lang('messages.already_have_account')</h5>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@fat"
                                class="btn btn-small">@lang('messages.login') <i class="fi-rs-arrow-right"></i></a>
                            @endguest
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                                aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="exampleModalLabel">Se connecter</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                            <form method="POST" wire:submit.prevent="login_submit">
                                                @csrf
                                                <div class="mb-3">
                                                    <x-input-label :value="__('Email')" />
                                                    <x-text-input wire:model='email' class="block mt-1 w-full"
                                                        type="email" name="email" :value="old('email')" required
                                                        autofocus autocomplete="username" />
                                                    @error('email')
                                                    <span class="error text-sm text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <x-input-label :value="__('Password')" />

                                                    <x-text-input wire:model='password' class="block mt-1 w-full"
                                                        type="password" name="password" required
                                                        autocomplete="current-password" />
                                                    @error('password')
                                                    <span class="text-sm text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="mb-3 row">
                                                    <span wire:loading
                                                        class="col-md-3 offset-md-5 text-brand">Processing...</span>
                                                </div>
                                                <div class="modal-footer">

                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Fermer</button>
                                                    <button type="submit" class="btn btn-primary">Valider</button>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <form method="post" class="needs-validation" action="{{ route('order.store') }}" novalidate>
                            @csrf
                            <input type="hidden" name="livraison" value="{{ $shipping ? $shipping->id : '' }}">
                            <div class="row">
                                <div class="col-md-6">
                                    <x-input type="text" place="votre prenom" :label="__('messages.first_name')"
                                        name="prenom" value="{{ $client ? $client->prenom : '' }}" />
                                </div>
                                <div class="col-md-6">
                                    <x-input type="text" place="votre nom" value="{{ $client ? $client->nom : '' }}"
                                        :label="__('messages.last_name')" name="nom" />
                                </div>
                                <div class="col-md-6">
                                    <x-input type="text" place="votre adresse"
                                        value="{{ $client ? $client->latestorder()->adresse : '' }}"
                                        :label="__('messages.address')" name="adresse" />
                                </div>
                                <div class="col-md-6">
                                    <x-input type="text" place="votre code postal"
                                        value="{{ $client ? $client->latestorder()->postal : '' }}" label="Postal"
                                        name="postal" />
                                </div>
                                <div class="col-md-6">
                                    <x-input type="text" place="votre ville"
                                        value="{{ $client ? $client->latestorder()->ville : '' }}"
                                        :label="__('messages.city')" name="ville" />
                                </div>
                                <div class="col-md-6">
                                    <div class="mt-3">
                                        <x-input type="text" place="votre contact"
                                            value="{{ $client ? $client->contact : '' }}"
                                            :label="__('messages.contact_with_code')" name="contact" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <p>
                                            @lang('messages.select_country_first')
                                        </p>
                                        <label
                                            class="text-uppercase form-label">@lang('messages.delivery_country')</label>
                                        <select class="form-select" required name="country_id"
                                            wire:model.live='country_id' wire:change='GetTrans'>
                                            <option selected value="">
                                                @lang('messages.select_country')
                                            </option>
                                            @foreach ($country as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->nom }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="valid-feedback"></div>
                                        <div class="invalid-feedback">Ce champ est obligatoire.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <x-input type="email" place="votre email" label="email"
                                        value="{{ $client ? $client->email : '' }}" name="email" />
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="text-uppercase form-label">@lang('messages.carrier')</label>
                                        <select class="form-select" required name="transport_id"
                                            wire:change="calculateShipping()" wire:model.live='transport_id'>
                                            <option selected value="">
                                                @lang('messages.select_carrier')
                                            </option>
                                            @foreach ($trans as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->nom }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="valid-feedback"></div>
                                        <div class="invalid-feedback">Ce champ est obligatoire.</div>
                                    </div>
                                </div>
                            </div>

                            @guest
                            <div class="form-group">
                                <div class="checkbox">
                                    <div class="custome-checkbox">
                                        <input class="form-check-input" type="checkbox" id="createaccount" />
                                        <label class="form-check-label label_info" data-bs-toggle="collapse"
                                            href="#collapsePassword" data-target="#collapsePassword"
                                            aria-controls="collapsePassword" for="createaccount"><span>
                                                @lang('messages.create_account_prompt')
                                            </span></label>
                                    </div>
                                </div>
                            </div>
                            <div id="collapsePassword" class="form-group create-account collapse in">
                                <x-input type="password" :required="false" place="entrez votre mot de passe" label=""
                                    name="password" />
                            </div>
                            @endguest

                            <div class="mb-20">
                                <h5>
                                    @lang('messages.add_comments_prompt')
                                </h5>
                            </div>
                            <div class="form-group mb-30">
                                <textarea name="commentaire" rows="5" placeholder="Order notes"></textarea>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-fill-out btn-block mt-30">
                                    Valider
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
</div>
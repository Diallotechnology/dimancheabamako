<div>
    <section class="mt-50 mb-50">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-20">
                        <h4>
                            @lang('messages.cart') {{ $items->count() }} element(s)
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
                                <tr wire:key="{{ $item->id }}">
                                    <td class="image product-thumbnail">
                                        <img src="{{ $item->associatedModel->cover }}" alt="cover" />
                                        <h5>{{ $item->name }}</h5>
                                    </td>
                                    <td class="text-center" data-title="Stock">
                                        <div class="border radius d-inline-flex">
                                            <livewire:update :row="$item" :key="'update-'.$item->id" />
                                        </div>
                                    </td>
                                    <td>{{ $item->associatedModel->prix_final }}</td>
                                    <td class="action" data-title="Remove">
                                        <livewire:delete :id="$item->id" :key="'delete-'.$item->id" />
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
                                        {{ session('locale') === 'fr' ? $shipping->montant_devise.' €' :
                                        $shipping->montant_devise.' $'
                                        }}
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
                                        {{ session('locale') === 'fr' ? ($Total + $shipping->montant_devise) . ' €' :
                                        ($Total + $shipping->montant_devise) . ' $' }}
                                        @else
                                        {{ session('locale') === 'fr' ? $Total . ' €' : $Total . ' $' }}
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
                    </div>
                    <form method="post" class="needs-validation" action="{{ route('order.store') }}" novalidate>
                        @csrf
                        <input type="hidden" name="livraison" value="{{ $shipping ? $shipping->id : '' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <x-input type="text" place="votre prenom" :label="__('messages.first_name')"
                                    name="prenom" />
                            </div>
                            <div class="col-md-6">
                                <x-input type="text" place="votre nom" :label="__('messages.last_name')" name="nom" />
                            </div>
                            <div class="col-md-6">
                                <x-input type="text" place="votre adresse" :label="__('messages.address')"
                                    name="adresse" />
                            </div>
                            <div class="col-md-6">
                                <x-input type="text" place="votre ville" :label="__('messages.city')" name="ville" />
                            </div>
                            <div class="col-md-6">
                                <div class="mt-3">
                                    <x-input type="text" place="votre contact" :label="__('messages.contact_with_code')"
                                        name="contact" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <x-input type="email" place="votre email" label="email" name="email" />
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <p>
                                        @lang('messages.select_country_first')
                                    </p>
                                    <label class="text-uppercase form-label">@lang('messages.delivery_country')</label>
                                    <select class="form-select" required name="country_id" wire:model.live='country_id'
                                        wire:change='GetTrans'>
                                        <option selected value="">
                                            @lang('messages.select_country')
                                        </option>
                                        @foreach ($country as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->nom }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <x-input type="text" place="votre code postal" label="Postal" name="postal" />
                            </div>

                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="text-uppercase form-label">@lang('messages.carrier')</label>
                                    <select class="form-select" required name="transport_id" wire:change="GetShipping()"
                                        wire:model.live='transport_id'>
                                        <option selected value="">
                                            @lang('messages.select_carrier')
                                        </option>
                                        @foreach ($trans as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->nom }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

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

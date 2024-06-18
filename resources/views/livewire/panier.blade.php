<div>
    <section class="mt-50 mb-50">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-20">
                        <h4>
                            Panier {{ $items->count() }} element
                        </h4>
                    </div>
                    <div class="table-responsive order_table text-center">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Prix unitaire</th>
                                    <th>Action</th>
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
                                            <livewire:update :row="$item" :key="$item->id" />
                                        </div>
                                    </td>
                                    <td>{{ $item->associatedModel->prix_final }}</td>
                                    <td class="action" data-title="Remove">
                                        <livewire:delete :id="$item->id" :key="$item->id" />
                                    </td>
                                </tr>
                                @empty
                                <h4 class="text-center my-5">
                                    Aucun produit dans le panier
                                </h4>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>
                <div @class(['col-md-4 order-1', 'd-none'=> $items->count() == 0])>
                    <h5>NB : Le paiement sera effectué en CFA (XOF).</h5>
                    <div class="table-responsive order_table text-center sticky-top">
                        <table class="table">
                            <tr>
                                <th>Poids Total</th>
                                <td>
                                    <em>{{ $totalWeight }}</em>
                                </td>
                            </tr>
                            <tr>
                                <th>Livraison</th>
                                <td>
                                    <em>{{ $shipping ? $shipping->montant_devise : '' }}</em>
                                </td>
                            </tr>
                            <tr>
                                <th>Delai de Livraison</th>
                                <td>
                                    <em>4 à 7 jours</em>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Quantity</th>
                                <td>
                                    <em>{{ $TotalQuantity }}</em>
                                </td>
                            </tr>
                            <tr>
                                <th>Total</th>
                                <td class="product-subtotal">
                                    <span class="font-xl text-brand fw-900">
                                        {{ session('locale') === 'fr' ? $Total.' €' : $Total.' $' }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div @class(['col-md-8', 'd-none'=> $items->count() == 0]) >
                    <div class="mb-25">
                        <h4>Informations de la commande</h4>
                    </div>
                    <form method="post" class="needs-validation" action="{{ route('order.store') }}" novalidate>
                        @csrf
                        <input type="hidden" name="livraison" value="{{ $shipping ? $shipping->id : '' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <x-input type="text" place="votre prenom" label="preNom" name="prenom" />
                            </div>
                            <div class="col-md-6">
                                <x-input type="text" place="votre nom" label="Nom" name="nom" />
                            </div>
                            <div class="col-md-6">
                                <x-input type="text" place="votre adresse" label="Adresse" name="adresse" />
                            </div>
                            <div class="col-md-6">
                                <x-input type="text" place="votre ville" label="Ville" name="ville" />
                            </div>
                            <div class="col-md-6">
                                <div class="mt-3">
                                    <x-input type="text" place="votre contact" label="contact (avec l'indicatif)"
                                        name="contact" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <x-input type="email" place="votre email" label="email" name="email" />
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <p>
                                        NB: en premier lieu, sélectionnez un
                                        pays
                                    </p>
                                    <label class="text-uppercase form-label">Pays de livraison</label>
                                    <select class="form-select" required name="country_id" wire:model.live='country_id'
                                        wire:change='GetTrans'>
                                        <option selected disabled value="">
                                            selectionner
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
                                    <label class="text-uppercase form-label">Transporteur</label>
                                    <select class="form-select" required name="transport_id" wire:change="GetShipping()"
                                        wire:model.live='transport_id'>
                                        <option selected disabled value="">
                                            selectionner un transporteur
                                        </option>
                                        @foreach ($trans as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->nom }}
                                        </option>
                                        @endforeach
                                        <option value="hhh">hhh</option>

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
                                        aria-controls="collapsePassword" for="createaccount"><span>Souhaitez vous crée
                                            un compte
                                            directement?</span></label>
                                </div>
                            </div>
                        </div>
                        <div id="collapsePassword" class="form-group create-account collapse in">
                            <x-input type="password" place="entrez votre mot de passe" label="" name="password" />
                        </div>

                        <div class="mb-20">
                            <h5>
                                Avez-vous des commentaire a ajouter a votre
                                commande ?
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
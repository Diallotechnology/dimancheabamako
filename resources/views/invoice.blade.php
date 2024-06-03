<!DOCTYPE html>
<html lang="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice</title>
    <link href="{{ asset('admin/assets/css/main.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
    <section class="content-main">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="receipt-content" style="background: none">
                            <div class="container bootstrap snippets bootdey">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="invoice-wrapper">
                                            <div class="payment-info">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <img src="{{ asset('assets/imgs/theme/logo.svg') }}" alt="logo"
                                                            width="70" />
                                                        <h5>Dimanche à Bamako</h5>
                                                        <h5>+223 66 03 51 54</h5>
                                                    </div>

                                                    <div class="col-sm-6 text-end">
                                                        <strong>
                                                            <i class="material-icons md-calendar_today"></i> <b>Date:{{
                                                                $order->created_at }}</b>
                                                        </strong>
                                                        <strong class="">Order ID: #{{ $order->reference }}</strong>
                                                        <strong>Payment Method: {{ $order->payment }}</strong>
                                                        <strong>Transport: {{ $order->transport->nom }}</strong>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="payment-details">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <span>Client</span>
                                                        <strong>
                                                            {{ $order->client->prenom }} {{ $order->client->nom }}
                                                        </strong>
                                                        <p>
                                                            {{ $order->client->contact }} <br>
                                                            {{ $order->client->pays }} <br>
                                                            <a href="#">
                                                                {{ $order->email }}
                                                            </a>
                                                        </p>
                                                    </div>
                                                    <div class="col-sm-6 text-end">
                                                        <span>Shipping Adress</span>
                                                        <strong>
                                                            {{ $order->adresse }}
                                                        </strong>
                                                        <p>
                                                            {{ $order->ville }} <br>
                                                            {{ $order->postal }}<br>
                                                            {{ $order->country->nom }} <br>

                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="line-items">
                                                <div class="headers clearfix">
                                                    <div class="row">
                                                        <div class="col-md-3">Reference</div>
                                                        <div class="col-md-3">Description</div>
                                                        <div class="col-md-3">Quantity</div>
                                                        <div class="col-md-3 text-end">Montant</div>
                                                    </div>
                                                </div>
                                                <div class="items">
                                                    @forelse ($order->products as $item)
                                                    <div class="row item">
                                                        <div class="col-md-3 desc">
                                                            {{ $item->reference }}
                                                        </div>
                                                        <div class="col-md-3 desc">
                                                            {{ $item->nom }}
                                                        </div>
                                                        <div class="col-md-3 qty">
                                                            {{ $item->pivot->quantity }}
                                                        </div>
                                                        <div class="col-md-3 amount text-end">
                                                            {{ session('locale') === 'fr' ?
                                                            $item->getMontant(). ' €' : $order->getMontant().' $'
                                                            }}
                                                        </div>
                                                    </div>
                                                    @empty
                                                    <td>
                                                        <h6 class="mb-0 text-center">Aucun produit</h6>
                                                    </td>
                                                    @endforelse
                                                </div>
                                                <div class="total text-end">
                                                    <p class="extra-notes">
                                                        <strong>Extra Notes</strong>
                                                        Please send all items at the same time to shipping address by
                                                        next week.
                                                        Thanks a lot.
                                                    </p>
                                                    <div class="field">
                                                        Subtotal <span>{{ number_format($order->totaux /
                                                            $order->getTaux(), 2);
                                                            }}{{ session('locale') === 'fr' ? ' €' : ' $' }}</span>

                                                    </div>
                                                    <div class="field">
                                                        Shipping <span>{{ session('locale') === 'fr' ?
                                                            $order->getShipping(). ' €' : $order->getShipping().' $'
                                                            }}</span>
                                                    </div>

                                                    <div class="field grand-total">
                                                        Total <span>{{ number_format($order->totaux /
                                                            $order->getTaux(), 2) + $order->getShipping() }}
                                                            {{ session('locale') === 'fr' ? ' €' : ' $' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="footer">
                                            Copyright © 2024. Dimanche à Bamako
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- card-body end// -->
                </div> <!-- card end// -->
            </div>
        </div>
    </section>
    <script src="{{ asset('admin/assets/js/vendors/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendors/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendors/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendors/jquery.fullscreen.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vendors/chart.js') }}"></script>
</body>

</html>
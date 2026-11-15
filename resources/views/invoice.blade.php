<!DOCTYPE html>
<html lang="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice</title>
    <link href="{{ asset('css/invoice.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
    <section class="content-main">
        <div class="row">
            <div class="col-md-12">
                <div class="receipt-content" style="background: none">
                    <div id="printableArea" class="container bootstrap snippets bootdey">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="invoice-wrapper">
                                    <div class="payment-info">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <img src="{{ asset('images/logo.svg') }}" alt="logo"
                                                    width="70" />
                                                <h5>Dimanche à Bamako</h5>
                                                <h5>ACI 2000 près de la place CAN</h5>
                                                <h5>+223 66 03 51 54</h5>
                                            </div>

                                            <div class="col-sm-6 text-end">
                                                <strong>
                                                    <svg class="invoice-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 3h-1V1h-2v2H7V1H5v2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 18H4V8h16v13z"/></svg> <b>Date: {{
                                                        $order->created_at }}</b>
                                                </strong>
                                                <strong class="">Facture N°: #{{ $order->reference }}</strong>
                                                <strong>Transaction N°: {{ $order->trans_ref }}</strong>
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
                                                        {{ $order->client->email }}
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
                                                <div class="col-md-3">@lang('messages.quantity')</div>
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
                                                    {{ \App\Service\PriceService::format((float) $item->pivot->montant) }}
                                                </div>
                                            </div>
                                            @empty
                                            <td>
                                                <h6 class="mb-0 text-center">
                                                    @lang('messages.no_product_available')</h6>
                                            </td>
                                            @endforelse
                                        </div>
                                        <div class="total text-end">
                                            <div class="field">
                                                Subtotal <span>{{ \App\Service\PriceService::format((float) $order->totaux) }}</span>
                                            </div>
                                            <div class="field">
                                                Shipping <span>{{ \App\Service\PriceService::format((float) $order->shipping) }}</span>
                                            </div>

                                            <div class="field grand-total">
                                                Total <span>{{ \App\Service\PriceService::format((float) $order->totaux + (float) $order->shipping) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                <!-- card end// -->
            </div>
        </div>
    </section>
    <script>
        (function () {
        var printContents = document.getElementById("printableArea").innerHTML;
    var originalContents = document.body.innerHTML; document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
        })();
    </script>
</body>

</html>
@php
    use App\Service\PriceService;

    // Facture = montant réellement prélevé (XOF) : toujours en CFA, jamais selon la devise de la session.
    $money = fn (int|float|string|null $amount): string => PriceService::formatCfa((float) $amount);
    $shipping = (float) $order->shipping;
    $subtotal = (float) $order->totaux;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('messages.invoice.title') }} {{ $order->reference }} · Dimanche à Bamako</title>
    <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
    <link href="{{ asset('css/invoice.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
    <main id="printableArea" class="invoice">
        <header class="invoice-head">
            <div class="invoice-seller">
                <img src="{{ asset('images/logo.svg') }}" alt="Dimanche à Bamako" width="64" height="64">
                <div>
                    <p class="invoice-brand">Dimanche à Bamako</p>
                    <p>ACI 2000 près de la place CAN</p>
                    <p>+223 66 03 51 54</p>
                </div>
            </div>
            <div class="invoice-title">
                <h1>{{ __('messages.invoice.title') }}</h1>
                <p class="invoice-number">N° {{ $order->reference }}</p>
            </div>
        </header>

        <dl class="invoice-meta">
            <div>
                <dt>{{ __('messages.invoice.date') }}</dt>
                <dd>{{ $order->created_at }}</dd>
            </div>
            <div>
                <dt>{{ __('messages.invoice.transaction') }}</dt>
                <dd class="invoice-mono">{{ $order->trans_ref }}</dd>
            </div>
            <div>
                <dt>{{ __('messages.invoice.transport') }}</dt>
                <dd>{{ $order->transport->nom }}</dd>
            </div>
        </dl>

        <section class="invoice-parties">
            <div>
                <h2>{{ __('messages.invoice.client') }}</h2>
                <p class="invoice-strong">{{ $order->client->prenom }} {{ $order->client->nom }}</p>
                <p>{{ $order->client->contact }}</p>
                <p>{{ $order->client->pays }}</p>
                <p>{{ $order->client->email }}</p>
            </div>
            <div>
                <h2>{{ __('messages.invoice.shipping_address') }}</h2>
                <p class="invoice-strong">{{ $order->adresse }}</p>
                <p>{{ $order->ville }}</p>
                <p>{{ $order->postal }}</p>
                <p>{{ $order->country->nom }}</p>
            </div>
        </section>

        <table class="invoice-lines">
            <thead>
                <tr>
                    <th scope="col">{{ __('messages.invoice.reference') }}</th>
                    <th scope="col">{{ __('messages.invoice.description') }}</th>
                    <th scope="col" class="num">{{ __('messages.quantity') }}</th>
                    <th scope="col" class="num">{{ __('messages.invoice.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($order->products as $item)
                <tr>
                    <td class="invoice-mono">{{ $item->reference }}</td>
                    <td>{{ $item->nom }}</td>
                    <td class="num">{{ $item->pivot->quantity }}</td>
                    <td class="num">{{ $money($item->pivot->montant) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="invoice-empty">{{ __('messages.no_product_available') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <dl class="invoice-totals">
            <div>
                <dt>{{ __('messages.invoice.subtotal') }}</dt>
                <dd>{{ $money($subtotal) }}</dd>
            </div>
            <div>
                <dt>{{ __('messages.invoice.shipping') }}</dt>
                <dd>{{ $money($shipping) }}</dd>
            </div>
            <div class="invoice-grand">
                <dt>{{ __('messages.invoice.total') }}</dt>
                <dd>{{ $money($subtotal + $shipping) }}</dd>
            </div>
        </dl>

        <footer class="invoice-foot">
            <p>{{ __('messages.invoice.thanks') }}</p>
        </footer>
    </main>
    <script>
        (function () {
            var printContents = document.getElementById("printableArea").outerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        })();
    </script>
</body>

</html>

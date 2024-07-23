<x-mail::message>
    <h3>Bonjour {{ $order->client->prenom }},</p>

        <p>@lang('messages.out_of_stock_message')</p>
        <p>@lang('messages.refund_message')</p>
        <p>@lang('messages.apology_message')</p>
        Merci,<br>
        {{ config('app.name') }}
</x-mail::message>
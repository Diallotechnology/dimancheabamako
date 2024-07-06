<x-mail::message>
    {{ __('messages.thank_you') }}
    <h2>{{ __('messages.order_success') }}</h2>
    <h4>{{ __('messages.thank_you_purchase') }} </h4>
    <x-mail::button :url="$url">
        {{ __('messages.download_invoice') }}
    </x-mail::button>
    {{ __('messages.thank_you') }},<br>
    {{ config('app.name') }}
</x-mail::message>
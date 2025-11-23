@php
    $url = route('confirm-registration',$token);
@endphp

<x-mail::message>

{{ __('messages.email.register.greeting') }}

{{ __('messages.email.register.thanks') }}

<x-mail::button :url="$url">
    {{ __('messages.email.register.button') }}
</x-mail::button>

{{ __('messages.email.register.expires') }}

{{ __('messages.email.register.regards') }}
{{ config('app.name') }}

</x-mail::message>

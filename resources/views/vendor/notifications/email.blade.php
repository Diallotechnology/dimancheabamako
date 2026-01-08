<x-mail::message>
# {{ __('passwords.greeting') }}

{{ __('passwords.intro') }}

{{ __('passwords.instruction') }}

<x-mail::button :url="$actionUrl">
{{ __('passwords.button') }}
</x-mail::button>

{{ __('passwords.fallback') }}

<span class="break-all">[{{ $actionUrl }}]({{ $actionUrl }})</span>

{{ __('passwords.ignore') }}

{{ __('passwords.closing') }}<br>
<strong>Le Service Clients</strong><br>
Dimanche à Bamako<br>
📞 Tel / WhatsApp : +223 66 03 51 54
</x-mail::message>

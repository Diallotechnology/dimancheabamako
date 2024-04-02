<x-mail::message>
    Nom: {{ $data['name'] }}
    email: {{ $data['email'] }}
    objet: {{ $data['subject'] }}
    <p>{{ $data['message'] }}</p>
    Merci,<br>
    {{ config('app.name') }}
</x-mail::message>
<x-mail::message>
    <h4> Nom: {{ $data['name'] }}</h4>
    <p>email: {{ $data['email'] }}</p>
    <p>objet: {{ $data['subject'] }}</p>
    <p>{{ $data['message'] }}</p>
    Merci,<br>
    {{ config('app.name') }}
</x-mail::message>

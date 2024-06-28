<x-mail::message>
    <h4>Le site a été mis en mode maintenance avec succès.</h4>
    <h5>Vous pouvez continuer à y accéder en utilisant ce token {{ $token }}</h5>
    Merci,<br>
    {{ config('app.name') }}
</x-mail::message>
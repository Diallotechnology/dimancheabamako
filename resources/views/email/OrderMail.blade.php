<x-mail::message>
    Bonjour
    <h2>Votre commande a été effectuer avec success</h2>
    <h4>Merci pour votre achat ! </h4>
    <x-mail::button :url="$url">
        Telecharger la facture
    </x-mail::button>
    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>
<x-mail::message>
    Salut {{ $name }}
    <h4>Nous sommes ravis de vous accueillir dans la famille Dimanche à Bamako!</h4>
    <p>Merci de vous être inscrit sur notre site. Nous sommes déterminés à vous offrir les meilleurs tissus et services
        pour tous vos projets d'habillement.</p>
    <x-mail::button url="http://dimancheabamako.com">
        Visitez notre boutique
    </x-mail::button>
    <p>
        Si vous avez des questions ou besoin d'assistance, notre équipe est à votre disposition. Vous pouvez nous
        contacter par e-mail à contact <a href="http://dimancheabamako.com">dimancheabamako.com</a> ou par
        téléphone/whatsapp au +223 66 03 51 54.
    </p>
    <p>Nous vous souhaitons une agréable expérience de shopping !</p>
    <p>Cordialement,</p>
    <p>Service client</p>
    <img src="{{ $message->embed(asset('assets/imgs/theme/logo_meta_tag.png')) }}" width="100" alt="logo">
    Merci,<br>
    {{ config('app.name') }}
</x-mail::message>
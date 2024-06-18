<x-app-layout>
    <section class="section-padding">
        <div class="container pt-25">
            <div class="row">
                <div class="col-md-6 m-auto">
                    <p class="mb-50"></p>
                    <h1 class="mb-4">{{ GoogleTranslate::trans("Opération terminée.", session('locale')) }}</h1>
                    <h4 class="mb-4">
                        {{ GoogleTranslate::trans("Félicitations, votre paiement a été effectué avec
                        succès.", session('locale')) }}
                       
                    </h4>
                    <h4 class="mb-4">
                        {{ GoogleTranslate::trans("Vous allez bientot recevoir un email de confirmation
                        avec la facture.", session('locale')) }}
                    </h4>
                    <a class="btn btn-default submit-auto-width font-xs hover-up" href="{{ route('home') }}">
                        {{ GoogleTranslate::trans("Retour à la page d'accueil", session('locale')) }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
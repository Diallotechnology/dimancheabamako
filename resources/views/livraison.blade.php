<x-app-layout>
    <section class="section-padding">
        <div class="container pt-25">
            <div class="row">
                <div class="col-lg-7 align-self-center mb-lg-0 mb-4">
                    <div class="single-content m-5">
                        <h4>{{ GoogleTranslate::trans("Délai et conditions de livraison", session('locale')) }}</h4>
                        <p>
                            {{ GoogleTranslate::trans("Les commandes passées sur ce site sont livrés
                            par DHL avec suivi. Le délai varie selon la
                            destination. En moyenne 4 à 6 jours ouvrables
                            entre l’achat et la livraison du colis.", session('locale')) }}
                            
                        </p>
                        <p>
                            {{ GoogleTranslate::trans("Nous livrons en Europe, USA, Canada directement
                            à votre adresse. Pour les livraisons en Afrique,
                            les colis sont à retirer dans les agences DHL
                            les plus proches. Pour cela assurez-vous avoir
                            fournir un numéro de téléphone joignable.", session('locale')) }}
                           
                        </p>
                        <p>
                            {{ GoogleTranslate::trans("Excepté les USA, les autorités des différents
                            pays sont susceptibles de facturer des frais de
                            douanes à l’arrivée du colis. Ces frais s’il y a
                            lieu sont entièrement à votre charge. Les
                            livraisons des colis sont garanties à 100%.
                            Toute commande non livrée sera intégralement
                            rembourser.", session('locale')) }}
                        </p>

                        <h4>Contact nous</h4>
                        <p>
                            DIMANCHE A BAMAKO <br />
                            ACI 2 000, près de la place CAN Bamako<br />
                            Malitel : +223 66 03 51 54
                        </p>
                    </div>
                </div>
                <div class="col-lg-5">
                    <img src="{{ asset('assets/imgs/theme/DHL.jpg') }}" alt="dhl" />
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
<x-app-layout>
    <div class="page-header breadcrumb-wrap">
        <div class="container">
            <div class="breadcrumb">
                <a href="/" rel="nofollow">@lang('messages.home')</a>
                <span></span> Pages <span></span> @lang('messages.contact_us')
            </div>
        </div>
    </div>
    <section class="section-border pt-50 pb-50">
        <div class="container">
            <!-- <div id="map-panes" class="leaflet-map mb-50"></div> -->
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <h4 class="mb-15 text-brand">Boutique</h4>
                    ACI 2 000<br />
                    <abbr title="Phone">Phone:</abbr> +223 66 03 51 54<br />
                    <abbr title="Email">Email: </abbr>contact@dimancheabamako.com<br />
                    <a
                        class="btn btn-outline btn-sm btn-brand-outline font-weight-bold text-brand bg-white text-hover-white mt-20 border-radius-5 btn-shadow-brand hover-up">
                        <i class="fi-rs-marker mr-10"></i> Voir sur map</a>
                </div>
            </div>
        </div>
    </section>
    <section class="pt-50 pb-50">
        <div class="container">
            <div class="row">
                <div class="col-xl-8 col-lg-10 m-auto">
                    <div class="contact-from-area padding-20-row-col wow FadeInUp">
                        <h3 class="mb-10 text-center">@lang('messages.contact_us')</h3>

                        @if (session('email_success'))
                        <div class="alert alert-success text-white text-center" style="background: #07bc0c"
                            role="alert">
                            {{ $email_success }}
                        </div>
                        @endif

                        <form class="contact-form-style text-center needs-validation" id="contact-form"
                            action="{{ route('contact.email') }}" method="post" novalidate>
                            @csrf
                            <div class="row">
                                <div class="col-lg-6 col-md-6">
                                    <div class="input-style mb-20">
                                        <input name="name" placeholder="votre nom" type="text" required />
                                        <div class="valid-feedback"></div>
                                        <div class='invalid-feedback'>Ce champ est
                                            obligatoire.</div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <div class="input-style mb-20">
                                        <input name="email" placeholder="Votre Email" type="email" required />
                                        <div class="valid-feedback"></div>
                                        <div class='invalid-feedback'>Ce champ est
                                            obligatoire.</div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="input-style mb-20">
                                        <input name="subject" placeholder="Votre Objet" type="text" required />
                                        <div class="valid-feedback"></div>
                                        <div class='invalid-feedback'>Ce champ est
                                            obligatoire.</div>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-12">
                                    <div class="textarea-style mb-30">
                                        <textarea name="message" placeholder="votre Message" required></textarea>
                                        <div class="valid-feedback"></div>
                                        <div class='invalid-feedback'>Ce champ est
                                            obligatoire.</div>
                                    </div>
                                    <button class="submit submit-auto-width" type="submit">
                                        Envoyé
                                    </button>
                                </div>
                            </div>
                        </form>
                        <p class="form-messege"></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>

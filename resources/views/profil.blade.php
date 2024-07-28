<x-app-layout>
    <section class="section-padding">
        <div class="container pt-25">
            <div class="row">
                <div class="col-md-8 m-auto">
                    <div class="card">
                        <div class="card-header">
                            <h5>Details du compte</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" class="needs-validation"
                                action="{{ route('profil.update',Auth::user()) }}" novalidate>
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="form-group col-md-12">

                                        <x-input type="text" place="votre nom d'utilisateur" label="Username *"
                                            name="name" value="{{ Auth::user()->name }}" />
                                    </div>
                                    <div class="form-group col-md-12">
                                        <x-input type="email" place="votre email" label="Email" name="email"
                                            value="{{ Auth::user()->email }}" />
                                    </div>
                                    <div class="form-group col-md-12">
                                        <x-input-label for="password" :value="__('messages.new_password')" />

                                        <x-text-input id="password" class="block mt-1 w-full" type="password"
                                            name="password" autocomplete="new-password" />

                                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                    </div>
                                    <div class="form-group col-md-12">
                                        <x-input-label for="password_confirmation"
                                            :value="__('messages.confirm_password')" />

                                        <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                            type="password" name="password_confirmation" autocomplete="new-password" />

                                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-fill-out submit">Enregistré</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>